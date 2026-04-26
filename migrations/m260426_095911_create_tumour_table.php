<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tumour}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%patient}}`
 */
class m260426_095911_create_tumour_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tumour}}', [
            'id' => $this->primaryKey(),
            'patient_id' => $this->integer()->notNull(),
            'incident_date' => $this->date(),
            'basis_of_diagnosis' => $this->integer(),
            'primary_site' => $this->string(),
            'laterality' => $this->integer(),
            'histology' => $this->integer(),
            'behaviour' => $this->integer(),
            'grade' => $this->integer(),
            'stage' => $this->integer(),
            't' => $this->string(),
            'n' => $this->string(),
            'm' => $this->string(),
            'full_tnm' => $this->boolean(),
            'metastasis' => $this->integer(),
            'regional_nodes_involvement' => $this->integer(),
            'localized_advanced' => $this->integer(),
            'localized_limited' => $this->integer(),
            'created_at' => $this->integer(25),
            'updated_at' => $this->integer(25),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // creates index for column `patient_id`
        $this->createIndex(
            '{{%idx-tumour-patient_id}}',
            '{{%tumour}}',
            'patient_id'
        );

        // add foreign key for table `{{%patient}}`
        $this->addForeignKey(
            '{{%fk-tumour-patient_id}}',
            '{{%tumour}}',
            'patient_id',
            '{{%patient}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%patient}}`
        $this->dropForeignKey(
            '{{%fk-tumour-patient_id}}',
            '{{%tumour}}'
        );

        // drops index for column `patient_id`
        $this->dropIndex(
            '{{%idx-tumour-patient_id}}',
            '{{%tumour}}'
        );

        $this->dropTable('{{%tumour}}');
    }
}
