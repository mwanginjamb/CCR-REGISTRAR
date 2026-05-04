<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%treatment}}`.
 */
class m260504_200220_create_treatment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%treatment}}', [
            'id' => $this->primaryKey(),
            'treatment' => $this->string(),
            'treatment_status' => $this->integer(),
            'treatment_date' => $this->date(),
            'patient_id' => $this->integer(),
            'concurrent_illness' => $this->text(),
            'created_at' => $this->integer(25),
            'updated_at' => $this->integer(25),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%treatment}}');
    }
}
