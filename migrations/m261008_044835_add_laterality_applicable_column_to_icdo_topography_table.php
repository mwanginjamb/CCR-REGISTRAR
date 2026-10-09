<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%icdo_topography}}`.
 */
class m261008_044835_add_laterality_applicable_column_to_icdo_topography_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%icdo_topography}}', 'laterality_applicable', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%icdo_topography}}', 'laterality_applicable');
    }
}
