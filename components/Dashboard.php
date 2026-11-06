<?php

namespace app\components;

use app\models\IcdoTopography;
use app\models\PatientReview;
use app\models\Tumour;
use Yii;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;

/**
 * Registry metrics for the Overview page (site/index).
 *
 * Every metric counts active patients only (soft-deleted patients excluded).
 * Methods return plain arrays, so the view only formats and draws them.
 *
 * Registry quality indicators follow IARC conventions, using the REDCap basis
 * of diagnosis codes:
 *   MV%  — morphologically verified: cytology/haematology (5), histology of
 *          metastasis (6) or histology of primary (7)
 *   DCO% — death certificate only (0)
 * Both are computed over cases with a recorded basis of diagnosis.
 */
class Dashboard
{
    /** Tumour fields every complete abstract must have */
    public const CORE_FIELDS = [
        'incident_date' => 'Incidence date',
        'basis_of_diagnosis' => 'Basis of diagnosis',
        'primary_site' => 'Primary site',
        'histology' => 'Histology',
        'behaviour' => 'Behaviour',
        'stage' => 'Stage',
    ];

    public const MV_CODES = [5, 6, 7];
    public const DCO_CODE = 0;

    private Connection $db;
    private int $now;

    /**
     * @param int|null $now timestamp treated as "now" (for tests); defaults to time()
     */
    public function __construct(?Connection $db = null, ?int $now = null)
    {
        $this->db = $db ?? Yii::$app->db;
        $this->now = $now ?? time();
    }

    /* ─── Headline numbers ───────────────────────────────────────────────── */

    public function totalCases(): int
    {
        return (int) $this->patients()->count('*', $this->db);
    }

    /**
     * Cases registered this calendar month, and last month for comparison.
     * @return array{current: int, previous: int, change: ?float} change in % (null when last month was 0)
     */
    public function newCasesThisMonth(): array
    {
        $startThis = strtotime(date('Y-m-01 00:00:00', $this->now));
        $startPrev = strtotime('-1 month', $startThis);

        $current = (int) $this->patients()->andWhere(['>=', 'p.created_at', $startThis])->andWhere(['<=', 'p.created_at', $this->now])->count('*', $this->db);
        $previous = (int) $this->patients()->andWhere(['>=', 'p.created_at', $startPrev])->andWhere(['<', 'p.created_at', $startThis])->count('*', $this->db);

        return [
            'current' => $current,
            'previous' => $previous,
            'change' => $previous > 0 ? round(($current - $previous) / $previous * 100, 1) : null,
        ];
    }

    /**
     * Review workload.
     * @return array{flagged: int, awaitingRegistrar: int, awaitingQa: int, resolved30d: int}
     */
    public function reviewStatus(): array
    {
        $reviews = fn() => (new Query())
            ->from(['r' => PatientReview::tableName()])
            ->innerJoin(['p' => '{{%patient}}'], 'p.id = r.patient_id AND p.is_deleted = 0');

        return [
            'flagged' => (int) $this->patients()->andWhere(['p.needs_review' => 1])->count('*', $this->db),
            'awaitingRegistrar' => (int) $reviews()->andWhere(['r.status' => PatientReview::STATUS_OPEN])->count('*', $this->db),
            'awaitingQa' => (int) $reviews()->andWhere(['r.status' => PatientReview::STATUS_RESUBMITTED])->count('*', $this->db),
            'resolved30d' => (int) $reviews()
                ->andWhere(['r.status' => PatientReview::STATUS_RESOLVED])
                ->andWhere(['>=', 'r.resolved_at', $this->now - 30 * 86400])
                ->count('*', $this->db),
        ];
    }

    /* ─── Data quality ───────────────────────────────────────────────────── */

    /**
     * Share of cases with each core tumour field recorded, plus the share with
     * all of them ("complete abstracts").
     *
     * @return array{total: int, complete: int, completePct: ?float, fields: array<string, array{label: string, filled: int, pct: ?float}>}
     */
    public function completeness(): array
    {
        $total = $this->totalCases();

        $select = [];
        $allFilled = [];
        foreach (array_keys(self::CORE_FIELDS) as $field) {
            // Coded fields are integers where 0 is a real value (stage 0 = in situ,
            // basis 0 = DCO); comparing them with '' would treat 0 as empty in MySQL
            $condition = $field === 'primary_site'
                ? "t.[[$field]] IS NOT NULL AND t.[[$field]] <> ''"
                : "t.[[$field]] IS NOT NULL";
            $select[$field] = new Expression("SUM(CASE WHEN $condition THEN 1 ELSE 0 END)");
            $allFilled[] = "($condition)";
        }
        $select['complete'] = new Expression('SUM(CASE WHEN ' . implode(' AND ', $allFilled) . ' THEN 1 ELSE 0 END)');

        $row = $this->patients()
            ->leftJoin(['t' => Tumour::tableName()], 't.patient_id = p.id')
            ->select($select)
            ->one($this->db) ?: [];

        $fields = [];
        foreach (self::CORE_FIELDS as $field => $label) {
            $filled = (int) ($row[$field] ?? 0);
            $fields[$field] = ['label' => $label, 'filled' => $filled, 'pct' => $this->pct($filled, $total)];
        }

        $complete = (int) ($row['complete'] ?? 0);

        return [
            'total' => $total,
            'complete' => $complete,
            'completePct' => $this->pct($complete, $total),
            'fields' => $fields,
        ];
    }

    /**
     * IARC quality indicators over cases with a recorded basis of diagnosis.
     * @return array{withBasis: int, mv: int, mvPct: ?float, dco: int, dcoPct: ?float}
     */
    public function qualityIndicators(): array
    {
        $counts = $this->basisCounts();
        $withBasis = array_sum($counts);

        $mv = 0;
        foreach (self::MV_CODES as $code) {
            $mv += $counts[$code] ?? 0;
        }
        $dco = $counts[self::DCO_CODE] ?? 0;

        return [
            'withBasis' => $withBasis,
            'mv' => $mv,
            'mvPct' => $this->pct($mv, $withBasis),
            'dco' => $dco,
            'dcoPct' => $this->pct($dco, $withBasis),
        ];
    }

    /* ─── Distributions ──────────────────────────────────────────────────── */

    /**
     * Cases per primary site category (C50.9 and C50.1 both count as C50),
     * largest first, with the remainder folded into "Other sites".
     *
     * @return array<int, array{key: string, label: string, count: int, pct: ?float}>
     */
    public function casesBySite(int $limit = 8): array
    {
        $rows = $this->patients()
            ->innerJoin(['t' => Tumour::tableName()], 't.patient_id = p.id')
            ->select(['category' => new Expression('LEFT(t.primary_site, 3)'), 'n' => new Expression('COUNT(*)')])
            ->andWhere(['not', ['t.primary_site' => null]])
            ->andWhere(['<>', 't.primary_site', ''])
            ->groupBy(new Expression('LEFT(t.primary_site, 3)'))
            ->orderBy(['n' => SORT_DESC, 'category' => SORT_ASC])
            ->all($this->db);

        $total = array_sum(array_column($rows, 'n'));
        $names = $this->siteNames();

        $result = [];
        $other = 0;
        foreach ($rows as $i => $row) {
            if ($i < $limit) {
                $result[] = [
                    'key' => $row['category'],
                    'label' => $names[$row['category']] ?? $row['category'],
                    'count' => (int) $row['n'],
                    'pct' => $this->pct((int) $row['n'], $total),
                ];
            } else {
                $other += (int) $row['n'];
            }
        }
        if ($other > 0) {
            $result[] = ['key' => 'other', 'label' => 'Other sites', 'count' => $other, 'pct' => $this->pct($other, $total)];
        }

        return $result;
    }

    /**
     * Cases registered per month for the last $months months, oldest first;
     * months without registrations are included as 0.
     *
     * @return array<int, array{key: string, label: string, count: int}>
     */
    public function registrationsByMonth(int $months = 12): array
    {
        $start = strtotime(date('Y-m-01 00:00:00', strtotime('-' . ($months - 1) . ' months', strtotime(date('Y-m-01', $this->now)))));

        // Bucket in PHP so months follow the app's time zone, not MySQL's
        $byMonth = [];
        $timestamps = $this->patients()
            ->select('p.created_at')
            ->andWhere(['>=', 'p.created_at', $start])
            ->andWhere(['<=', 'p.created_at', $this->now])
            ->column($this->db);
        foreach ($timestamps as $ts) {
            $key = date('Y-m', (int) $ts);
            $byMonth[$key] = ($byMonth[$key] ?? 0) + 1;
        }

        $result = [];
        for ($i = 0; $i < $months; $i++) {
            $ts = strtotime("+$i months", $start);
            $key = date('Y-m', $ts);
            $result[] = ['key' => $key, 'label' => date('M Y', $ts), 'count' => (int) ($byMonth[$key] ?? 0)];
        }

        return $result;
    }

    /**
     * Cases per stage, in stage order (dictionary codes), then "Not recorded".
     * @return array<int, array{key: string, label: string, count: int, pct: ?float}>
     */
    public function stageDistribution(): array
    {
        return $this->codedDistribution('stage', Tumour::getStage());
    }

    /**
     * Cases per basis of diagnosis, in code order, then "Not recorded".
     * @return array<int, array{key: string, label: string, count: int, pct: ?float}>
     */
    public function basisDistribution(): array
    {
        return $this->codedDistribution('basis_of_diagnosis', Tumour::getBasisOfDiagnosis());
    }

    /* ─── Activity ───────────────────────────────────────────────────────── */

    /**
     * Registrars who registered the most cases in the last $days days.
     * @return array<int, array{username: string, count: int}>
     */
    public function topRegistrars(int $days = 30, int $limit = 5): array
    {
        $rows = $this->patients()
            ->innerJoin(['u' => '{{%user}}'], 'u.id = p.created_by')
            ->select(['username' => 'u.username', 'n' => new Expression('COUNT(*)')])
            ->andWhere(['>=', 'p.created_at', $this->now - $days * 86400])
            ->groupBy('u.id')
            ->orderBy(['n' => SORT_DESC, 'username' => SORT_ASC])
            ->limit($limit)
            ->all($this->db);

        return array_map(fn($r) => ['username' => $r['username'], 'count' => (int) $r['n']], $rows);
    }

    /* ─── Helpers ────────────────────────────────────────────────────────── */

    /** Active (not soft-deleted) patients, aliased "p" */
    private function patients(): Query
    {
        return (new Query())->from(['p' => '{{%patient}}'])->where(['p.is_deleted' => 0]);
    }

    /** @return array<int, int> basis code => cases (recorded codes only) */
    private function basisCounts(): array
    {
        $rows = $this->patients()
            ->innerJoin(['t' => Tumour::tableName()], 't.patient_id = p.id')
            ->select(['code' => 't.basis_of_diagnosis', 'n' => new Expression('COUNT(*)')])
            ->andWhere(['not', ['t.basis_of_diagnosis' => null]])
            ->groupBy('t.basis_of_diagnosis')
            ->all($this->db);

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['code']] = (int) $row['n'];
        }
        return $counts;
    }

    /**
     * Counts of a coded tumour field in dictionary order, then "Not recorded"
     * (no tumour row, or the field empty).
     */
    private function codedDistribution(string $field, array $options): array
    {
        $rows = $this->patients()
            ->leftJoin(['t' => Tumour::tableName()], 't.patient_id = p.id')
            ->select(['code' => "t.$field", 'n' => new Expression('COUNT(*)')])
            ->groupBy("t.$field")
            ->all($this->db);

        $counts = [];
        $missing = 0;
        foreach ($rows as $row) {
            if ($row['code'] === null || $row['code'] === '') {
                $missing += (int) $row['n'];
            } else {
                $counts[(string) $row['code']] = (int) $row['n'];
            }
        }
        $total = array_sum($counts) + $missing;

        $result = [];
        foreach ($options as $code => $label) {
            $n = $counts[(string) $code] ?? 0;
            unset($counts[(string) $code]);
            $result[] = ['key' => (string) $code, 'label' => (string) $label, 'count' => $n, 'pct' => $this->pct($n, $total)];
        }
        // Codes no longer in the dictionary still count, labelled by code
        foreach ($counts as $code => $n) {
            $result[] = ['key' => (string) $code, 'label' => "Code $code", 'count' => $n, 'pct' => $this->pct($n, $total)];
        }
        $result[] = ['key' => 'missing', 'label' => 'Not recorded', 'count' => $missing, 'pct' => $this->pct($missing, $total)];

        return $result;
    }

    /** @return array<string, string> "C50" => "Breast, NOS" (from the category's .9 code, else its first code) */
    private function siteNames(): array
    {
        $names = [];
        foreach (IcdoTopography::find()->select(['code', 'description'])->orderBy(['code' => SORT_ASC])->asArray()->all($this->db) as $row) {
            $category = substr($row['code'], 0, 3);
            // Prefer the category's .9 "NOS" name; otherwise keep its first code's name
            if (!isset($names[$category]) || str_ends_with($row['code'], '.9')) {
                $names[$category] = $row['description'];
            }
        }
        return $names;
    }

    private function pct(int $part, int $whole): ?float
    {
        return $whole > 0 ? round($part / $whole * 100, 1) : null;
    }
}
