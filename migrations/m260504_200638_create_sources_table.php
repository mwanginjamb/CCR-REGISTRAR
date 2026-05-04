<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sources}}`.
 */
class m260504_200638_create_sources_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sources}}', [
            'id' => $this->primaryKey(),
            'source_type' => $this->integer(),
            'source_no' => $this->string(),
            'source_date' => $this->date(),
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
        $this->dropTable('{{%sources}}');
    }
}
