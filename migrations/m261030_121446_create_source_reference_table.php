<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%source_reference}}`.
 *
 * Setup list of known sources (hospitals, labs, hospices) offered as suggestions
 * for "Source No" on the patient form. Not to be confused with `{{%sources}}`,
 * which holds each patient's source entries.
 *
 * source_type uses the same codes as sources.source_type
 * (Sources::getSourceTypeOptions(): 1 Hospital, 2 Lab, 3 Hospice).
 */
class m261030_121446_create_source_reference_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%source_reference}}', [
            'id' => $this->primaryKey(),
            'source_type' => $this->integer()->notNull(),
            'source_no' => $this->string(20)->notNull(),
            'name' => $this->string(255)->notNull(),
            'redcap_value' => $this->string(20),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $tableOptions);

        $this->createIndex(
            'uq-source_reference-source_type-source_no',
            '{{%source_reference}}',
            ['source_type', 'source_no'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%source_reference}}');
    }
}
