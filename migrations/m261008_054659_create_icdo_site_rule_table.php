<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%icdo_site_rule}}`.
 */
class m261008_054659_create_icdo_site_rule_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%icdo_site_rule}}', [
            'id' => $this->primaryKey(),
            'topography_pattern' => $this->string(50),
            'laterality_required' => $this->boolean(),
            'laterality_allowed' => $this->boolean(),
            'tnm_allowed' => $this->boolean(),
            'grade_allowed' => $this->boolean(),
            'created_at' => $this->integer(30),
            'updated_at' => $this->integer(30),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // Add unique index on topography_pattern
        $this->createIndex(
            'uq-icdo_site_rule-topography_pattern',
            '{{%icdo_site_rule}}',
            'topography_pattern',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%icdo_site_rule}}');
    }
}
