<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "patient_review".
 *
 * A QA review of a patient record: QA flags the case (open), the registrar
 * corrects and resubmits it (resubmitted), QA marks it reviewed (resolved).
 *
 * @property int $id
 * @property int $patient_id
 * @property string $reason
 * @property string $note
 * @property string $status
 * @property int|null $flagged_by
 * @property int $flagged_at
 * @property int|null $resubmitted_by
 * @property int|null $resubmitted_at
 * @property string|null $resubmit_note
 * @property int|null $resolved_by
 * @property int|null $resolved_at
 * @property string|null $resolution_note
 * @property string|null $notified_email
 * @property int|null $notified_at
 * @property string|null $notify_error
 *
 * @property Patient $patient
 * @property User|null $flaggedByUser
 * @property User|null $resubmittedByUser
 * @property User|null $resolvedByUser
 */
class PatientReview extends \yii\db\ActiveRecord
{
    public const STATUS_OPEN = 'open';
    public const STATUS_RESUBMITTED = 'resubmitted';
    public const STATUS_RESOLVED = 'resolved';

    /** Statuses that keep the patient flagged */
    public const ACTIVE_STATUSES = [self::STATUS_OPEN, self::STATUS_RESUBMITTED];

    public const REASONS = [
        'coding' => 'Incorrect coding (site, histology, stage)',
        'missing_data' => 'Missing or incomplete data',
        'dates' => 'Inconsistent dates',
        'sources' => 'Source information incomplete',
        'duplicate' => 'Possible duplicate record',
        'other' => 'Other',
    ];

    public const SCENARIO_FLAG = 'flag';
    public const SCENARIO_RESUBMIT = 'resubmit';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'patient_review';
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['reason', 'note', 'resubmit_note', 'resolution_note'],
            self::SCENARIO_FLAG => ['reason', 'note'],
            self::SCENARIO_RESUBMIT => ['resubmit_note'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['note', 'resubmit_note', 'resolution_note'], 'trim'],
            [['reason', 'note'], 'required', 'on' => self::SCENARIO_FLAG],
            ['reason', 'in', 'range' => array_keys(self::REASONS)],
            ['note', 'string', 'min' => 5, 'max' => 2000],
            [['resubmit_note', 'resolution_note'], 'string', 'max' => 2000],
            [['resubmit_note', 'resolution_note'], 'default', 'value' => null],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'patient_id' => 'Patient',
            'reason' => 'Reason',
            'note' => 'What needs correcting',
            'status' => 'Status',
            'flagged_by' => 'Flagged By',
            'flagged_at' => 'Flagged At',
            'resubmitted_by' => 'Resubmitted By',
            'resubmitted_at' => 'Resubmitted At',
            'resubmit_note' => 'Note to QA',
            'resolved_by' => 'Resolved By',
            'resolved_at' => 'Resolved At',
            'resolution_note' => 'Resolution Note',
            'notified_email' => 'Notified Email',
            'notified_at' => 'Notified At',
            'notify_error' => 'Notification Error',
        ];
    }

    public function getPatient()
    {
        return $this->hasOne(Patient::class, ['id' => 'patient_id']);
    }

    public function getFlaggedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'flagged_by']);
    }

    public function getResubmittedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'resubmitted_by']);
    }

    public function getResolvedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'resolved_by']);
    }

    public function getReasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? (string) $this->reason;
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    /**
     * Opens a QA review on a patient. Call validate() first (scenario flag).
     */
    public function flag(Patient $patient, int $qaUserId): bool
    {
        $this->patient_id = $patient->id;
        $this->status = self::STATUS_OPEN;
        $this->flagged_by = $qaUserId;
        $this->flagged_at = time();

        if (!$this->save(false)) {
            return false;
        }

        $patient->refreshReviewFlag();
        return true;
    }

    public function resubmit(int $userId, ?string $note): void
    {
        $this->status = self::STATUS_RESUBMITTED;
        $this->resubmitted_by = $userId;
        $this->resubmitted_at = time();
        $this->resubmit_note = $note !== null && trim($note) !== '' ? trim($note) : null;
        $this->save(false);
    }

    public function resolve(int $userId, ?string $note): void
    {
        $this->status = self::STATUS_RESOLVED;
        $this->resolved_by = $userId;
        $this->resolved_at = time();
        $this->resolution_note = $note !== null && trim($note) !== '' ? trim($note) : null;
        $this->save(false);
    }
}
