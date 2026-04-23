<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%patient}}`.
 */
class m260423_103052_create_patient_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%patient}}', [
            'id' => $this->primaryKey(),
            'full_name' => $this->string(250),
            'national_id' => $this->string(30),
            'telephone_no_patient' => $this->string(30),
            'telephone_no_nok' => $this->string(30),
            'age' => $this->integer(),
            'date_of_birth' => $this->date(),
            'place_of_birth' => $this->string(250),
            'ethnic_group' => $this->integer(),
            'religion' => $this->integer(),
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
        $this->dropTable('{{%patient}}');
    }
}
