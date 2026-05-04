<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%follow_up}}`.
 */
class m260504_200939_create_follow_up_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%follow_up}}', [
            'id' => $this->primaryKey(),
            'present_status' => $this->integer(),
            'cause_of_death' => $this->text(),
            'last_date_of_contact' => $this->date(),
            'remarks' => $this->text(),
            'patient_id' => $this->integer(),
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
        $this->dropTable('{{%follow_up}}');
    }
}
