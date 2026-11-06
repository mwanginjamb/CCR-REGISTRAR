<?php

namespace app\components;

use app\models\PatientReview;
use Yii;
use yii\db\Connection;
use yii\db\Query;

/**
 * Review items waiting on the current user, for the header notification bell.
 *
 *   admin      — every case flagged for review
 *   qa         — cases waiting on QA: reviews resubmitted by a registrar, and
 *                system-flagged cases that have no QA review yet
 *   registrar  — their own flagged cases that are waiting on them (cases
 *                already resubmitted to QA are not counted)
 *
 * The highest role wins (admin, then qa, then registrar); roles inherit, so
 * an admin also holds qa and registrar.
 */
class ReviewInbox
{
    public const SCOPE_ALL = 'all';
    public const SCOPE_QA = 'qa';
    public const SCOPE_OWN = 'own';

    private Connection $db;

    public function __construct(?Connection $db = null)
    {
        $this->db = $db ?? Yii::$app->db;
    }

    /** Which queue the current user sees, or null for guests / users with no review role */
    public static function scopeForCurrentUser(): ?string
    {
        $user = Yii::$app->user;
        if ($user->isGuest) {
            return null;
        }
        if ($user->can(Rbac::ROLE_ADMIN)) {
            return self::SCOPE_ALL;
        }
        if ($user->can(Rbac::REVIEW_PATIENT)) {
            return self::SCOPE_QA;
        }
        if ($user->can(Rbac::RESUBMIT_PATIENT_REVIEW)) {
            return self::SCOPE_OWN;
        }
        return null;
    }

    /** Number of review items for the current user (0 for guests) */
    public function countForCurrentUser(): int
    {
        $scope = self::scopeForCurrentUser();
        return $scope === null ? 0 : $this->count($scope, (int) Yii::$app->user->id);
    }

    public function count(string $scope, int $userId = 0): int
    {
        $query = (new Query())
            ->from(['p' => '{{%patient}}'])
            ->where(['p.is_deleted' => 0]);

        self::applyScope($query, $scope, $userId, 'p');

        return (int) $query->count('*', $this->db);
    }

    /**
     * Narrows a patient query to the review items of a scope. Used by both the
     * bell count and the patient list (?review=1), so the two always agree.
     *
     * @param Query  $query  a query over the patient table
     * @param string $alias  the patient table's alias (or table name) in $query
     */
    public static function applyScope(Query $query, string $scope, int $userId, string $alias = 'patient'): Query
    {
        $query->andWhere(["$alias.needs_review" => 1]);

        // QA reviews of the patient in the given statuses, for EXISTS / NOT EXISTS
        $review = fn(array $statuses) => (new Query())
            ->from(['r' => PatientReview::tableName()])
            ->where("r.patient_id = $alias.id")
            ->andWhere(['r.status' => $statuses]);

        switch ($scope) {
            case self::SCOPE_ALL:
                break;

            case self::SCOPE_QA:
                // Resubmitted by the registrar, or flagged by the system with no QA review open
                $query->andWhere([
                    'or',
                    ['exists', $review([PatientReview::STATUS_RESUBMITTED])],
                    ['not exists', $review(PatientReview::ACTIVE_STATUSES)],
                ]);
                break;

            case self::SCOPE_OWN:
                // Own cases, except those already sent back to QA
                $query->andWhere(["$alias.created_by" => $userId])
                    ->andWhere(['not exists', $review([PatientReview::STATUS_RESUBMITTED])]);
                break;

            default:
                $query->andWhere('0 = 1');
        }

        return $query;
    }

    /** Tooltip text for the bell */
    public static function describe(string $scope, int $count): string
    {
        $cases = $count === 1 ? '1 case' : "$count cases";

        return match ($scope) {
            self::SCOPE_ALL => $count ? "$cases flagged for review" : 'No cases flagged for review',
            self::SCOPE_QA => $count ? "$cases waiting for QA review" : 'Nothing waiting for QA review',
            self::SCOPE_OWN => $count ? "$cases of yours need correcting" : 'None of your cases need correcting',
            default => 'Review items',
        };
    }
}
