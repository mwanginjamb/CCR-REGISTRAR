<?php

namespace app\models\queries;

/**
 * This is the ActiveQuery class for [[\app\models\FollowUp]].
 *
 * @see \app\models\FollowUp
 */
class FollowUpQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\FollowUp[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\FollowUp|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
