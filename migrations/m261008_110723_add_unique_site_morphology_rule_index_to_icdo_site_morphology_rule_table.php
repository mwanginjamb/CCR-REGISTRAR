<?php

use yii\db\Migration;

class m261008_110723_add_unique_site_morphology_rule_index_to_icdo_site_morphology_rule_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'uq-site-morphology-rule',
            '{{%icdo_site_morphology_rule}}',
            [
                'topography_pattern',
                'morphology_code',
                'rule_type',
                'source_version',
            ],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'uq-site-morphology-rule',
            '{{%icdo_site_morphology_rule}}'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m261008_110723_add_unique_site_morphology_rule_index_to_icdo_site_morphology_rule_table cannot be reverted.\n";

        return false;
    }
    */
}
