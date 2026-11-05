<?php

use yii\db\Migration;

/**
 * Handles adding soft delete columns to table `{{%patient}}`.
 *
 * Deleting a patient (QA) sets these instead of removing the row, so the record
 * and its tumour, treatment, source and follow-up rows stay in the database.
 * Patient::find() hides deleted patients.
 */
class m261105_123747_add_soft_delete_columns_to_patient_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%patient}}', 'is_deleted', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%patient}}', 'deleted_at', $this->integer());
        $this->addColumn('{{%patient}}', 'deleted_by', $this->integer());

        $this->createIndex('idx-patient-is_deleted', '{{%patient}}', 'is_deleted');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-patient-is_deleted', '{{%patient}}');
        $this->dropColumn('{{%patient}}', 'deleted_by');
        $this->dropColumn('{{%patient}}', 'deleted_at');
        $this->dropColumn('{{%patient}}', 'is_deleted');
    }
}
