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
     * Patients created by the given user (a registrar's view of the registry),
     * whether or not they are flagged for review.
     */
    public function createdBy(int $userId)
    {
        return $this->andWhere(['patient.created_by' => $userId]);
    }

    /**
     * Patients the current user may see: all of them for qa/admin, otherwise
     * only those they created. See Rbac::canViewAllPatients().
     */
    public function visibleToCurrentUser()
    {
        if (\app\components\Rbac::canViewAllPatients()) {
            return $this;
        }
        return $this->createdBy((int) \Yii::$app->user->id);
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
