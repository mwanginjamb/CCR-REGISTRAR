<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%sources}}`.
 *
 * record_no is the patient's file/record number at the source (REDCap hosp_no /
 * lab_no / hospice_no); source_no identifies the source itself (source_reference).
 */
class m261030_122338_add_record_no_column_to_sources_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%sources}}', 'record_no', $this->string(50)->after('source_no'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%sources}}', 'record_no');
    }
}
