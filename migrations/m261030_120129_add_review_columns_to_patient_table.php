<?php

use yii\db\Migration;

/**
 * Handles adding review columns to table `{{%patient}}`.
 *
 * needs_review is raised when a save produces non-blocking validation warnings
 * (e.g. an unrecognised site/histology combination); review_note keeps those
 * warnings so a reviewer knows what to check.
 */
class m261030_120129_add_review_columns_to_patient_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%patient}}', 'needs_review', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%patient}}', 'review_note', $this->text());

        $this->createIndex('idx-patient-needs_review', '{{%patient}}', 'needs_review');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-patient-needs_review', '{{%patient}}');
        $this->dropColumn('{{%patient}}', 'review_note');
        $this->dropColumn('{{%patient}}', 'needs_review');
    }
}
