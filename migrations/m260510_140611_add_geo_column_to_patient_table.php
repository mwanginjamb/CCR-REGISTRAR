<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%patient}}`.
 */
class m260510_140611_add_geo_column_to_patient_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%patient}}', 'geo_lat', $this->string(25));
        $this->addColumn('{{%patient}}', 'geo_lng', $this->string(25));
        $this->addColumn('{{%patient}}', 'geo_accuracy', $this->string(25));
        $this->addColumn('{{%patient}}', 'geo_captured', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%patient}}', 'geo_lat');
        $this->dropColumn('{{%patient}}', 'geo_lng');
        $this->dropColumn('{{%patient}}', 'geo_accuracy');
        $this->dropColumn('{{%patient}}', 'geo_captured');
    }
}
