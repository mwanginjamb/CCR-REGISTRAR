<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%tumour}}`.
 *
 * topography_code ("C" on the CRF) holds the specific ICD-O sub-site within the
 * primary site category, e.g. primary_site C00.9 -> topography_code C00.1.
 */
class m261030_120130_add_topography_code_column_to_tumour_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%tumour}}', 'topography_code', $this->string(10)->after('primary_site'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%tumour}}', 'topography_code');
    }
}
