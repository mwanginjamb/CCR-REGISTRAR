<?php

namespace app\models\queries;

/**
 * This is the ActiveQuery class for [[\app\models\Patient]].
 *
 * @see \app\models\Patient
 */
class PatientQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * Patients created by the given user that are flagged for review
     * (the registrar's view of the patient list).
     */
    public function flaggedForReviewBy(int $userId)
    {
        return $this->andWhere([
            'patient.created_by' => $userId,
            'patient.needs_review' => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Patient[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Patient|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
