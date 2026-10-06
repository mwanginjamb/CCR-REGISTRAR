<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%redcap_dictionary}}`.
 */
class m261003_161251_create_redcap_dictionary_table extends Migration
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

        // ---------------------------------------------------------------
        // redcap_import_run
        // ---------------------------------------------------------------
        $this->createTable('{{%redcap_import_run}}', [
            'id' => $this->bigPrimaryKey(),
            'source_name' => $this->string(255)->notNull(),
            'source_hash' => $this->char(64)->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'row_count' => $this->integer()->notNull()->defaultValue(0),
            'field_count' => $this->integer()->notNull()->defaultValue(0),
            'option_count' => $this->integer()->notNull()->defaultValue(0),
            'warning_count' => $this->integer()->notNull()->defaultValue(0),
            'error_message' => $this->text(),
            'started_at' => $this->dateTime()->notNull(),
            'completed_at' => $this->dateTime(),
            'created_by' => $this->integer(),
        ], $tableOptions);

        $this->createIndex(
            'uq-redcap_import_run-source_hash',
            '{{%redcap_import_run}}',
            'source_hash',
            true
        );

        // ---------------------------------------------------------------
        // redcap_dictionary_field
        // ---------------------------------------------------------------
        $this->createTable('{{%redcap_dictionary_field}}', [
            'id' => $this->bigPrimaryKey(),
            'import_run_id' => $this->bigInteger()->notNull(),
            'field_name' => $this->string(191)->notNull(),
            'form_name' => $this->string(191),
            'section_header' => $this->text(),
            'field_type' => $this->string(40)->notNull(),
            'field_label' => $this->text(),
            'choices_text' => $this->text(),
            'field_note' => $this->text(),
            'validation_type' => $this->string(100),
            'validation_min' => $this->string(100),
            'validation_max' => $this->string(100),
            'is_identifier' => $this->boolean()->notNull()->defaultValue(false),
            'branching_logic' => $this->text(),
            'is_required' => $this->boolean()->notNull()->defaultValue(false),
            'field_annotation' => $this->text(),
            'row_number' => $this->integer()->notNull(),
            'row_hash' => $this->char(64)->notNull(),
            'raw_row_json' => $this->text()->notNull(),
        ], $tableOptions);

        $this->createIndex(
            'uq-redcap_dictionary_field-import_run_id-field_name',
            '{{%redcap_dictionary_field}}',
            ['import_run_id', 'field_name'],
            true
        );

        $this->addForeignKey(
            'fk-redcap_dictionary_field-import_run_id',
            '{{%redcap_dictionary_field}}',
            'import_run_id',
            '{{%redcap_import_run}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // ---------------------------------------------------------------
        // reference_set
        // ---------------------------------------------------------------
        $this->createTable('{{%reference_set}}', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string(191)->notNull(),
            'label' => $this->string(500),
            'source_type' => $this->string(50)->notNull()->defaultValue('redcap'),
            'fingerprint' => $this->char(64)->notNull(),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createIndex(
            'uq-reference_set-fingerprint',
            '{{%reference_set}}',
            'fingerprint',
            true
        );

        // ---------------------------------------------------------------
        // reference_option
        // ---------------------------------------------------------------
        $this->createTable('{{%reference_option}}', [
            'id' => $this->bigPrimaryKey(),
            'reference_set_id' => $this->bigInteger()->notNull(),
            'value' => $this->string(100)->notNull(),
            'label' => $this->text()->notNull(),
            'normalized_code' => $this->string(100),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'metadata_json' => $this->text(),
        ], $tableOptions);

        $this->createIndex(
            'uq-reference_option-reference_set_id-value',
            '{{%reference_option}}',
            ['reference_set_id', 'value'],
            true
        );

        $this->addForeignKey(
            'fk-reference_option-reference_set_id',
            '{{%reference_option}}',
            'reference_set_id',
            '{{%reference_set}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // ---------------------------------------------------------------
        // redcap_field_reference_set (junction table)
        // ---------------------------------------------------------------
        $this->createTable('{{%redcap_field_reference_set}}', [
            'field_id' => $this->bigInteger()->notNull(),
            'reference_set_id' => $this->bigInteger()->notNull(),
            'PRIMARY KEY([[field_id]], [[reference_set_id]])',
        ], $tableOptions);

        $this->createIndex(
            'idx-redcap_field_reference_set-reference_set_id',
            '{{%redcap_field_reference_set}}',
            'reference_set_id'
        );

        $this->addForeignKey(
            'fk-redcap_field_reference_set-field_id',
            '{{%redcap_field_reference_set}}',
            'field_id',
            '{{%redcap_dictionary_field}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-redcap_field_reference_set-reference_set_id',
            '{{%redcap_field_reference_set}}',
            'reference_set_id',
            '{{%reference_set}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // ---------------------------------------------------------------
        // icdo_topography
        // ---------------------------------------------------------------
        $this->createTable('{{%icdo_topography}}', [
            'id' => $this->bigPrimaryKey(),
            'redcap_value' => $this->string(100)->notNull(),
            'code' => $this->string(10)->notNull(),
            'description' => $this->text()->notNull(),
            'source_version' => $this->string(50),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createIndex(
            'uq-icdo_topography-code',
            '{{%icdo_topography}}',
            'code',
            true
        );

        // TEXT columns need a prefix length in MySQL indexes.
        $this->createIndex(
            'idx-icdo_topography-description',
            '{{%icdo_topography}}',
            'description(191)'
        );

        // ---------------------------------------------------------------
        // icdo_morphology
        // ---------------------------------------------------------------
        $this->createTable('{{%icdo_morphology}}', [
            'id' => $this->bigPrimaryKey(),
            'redcap_value' => $this->string(100)->notNull(),
            'code' => $this->string(10)->notNull(),
            'description' => $this->text()->notNull(),
            'behaviour_code' => $this->string(5),
            'source_version' => $this->string(50),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createIndex(
            'uq-icdo_morphology-code',
            '{{%icdo_morphology}}',
            'code',
            true
        );

        $this->createIndex(
            'idx-icdo_morphology-description',
            '{{%icdo_morphology}}',
            'description(191)'
        );

        // ---------------------------------------------------------------
        // icdo_site_morphology_rule
        //
        // Do not populate this table by combining every topography with
        // every morphology. Populate it only from an authoritative ICD-O
        // validation source.
        // ---------------------------------------------------------------
        $this->createTable('{{%icdo_site_morphology_rule}}', [
            'id' => $this->bigPrimaryKey(),
            'topography_pattern' => $this->string(20)->notNull(),
            'morphology_code' => $this->string(10)->notNull(),
            'rule_type' => $this->string(20)->notNull()->defaultValue('allowed'),
            'source_version' => $this->string(50),
            'note' => $this->text(),
        ], $tableOptions);

        $this->createIndex(
            'idx-icdo_site_morphology_rule-topography_pattern-morphology_code',
            '{{%icdo_site_morphology_rule}}',
            ['topography_pattern', 'morphology_code']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop in reverse order of creation so foreign keys never block.
        $this->dropTable('{{%icdo_site_morphology_rule}}');
        $this->dropTable('{{%icdo_morphology}}');
        $this->dropTable('{{%icdo_topography}}');
        $this->dropTable('{{%redcap_field_reference_set}}');
        $this->dropTable('{{%reference_option}}');
        $this->dropTable('{{%reference_set}}');
        $this->dropTable('{{%redcap_dictionary_field}}');
        $this->dropTable('{{%redcap_import_run}}');
    }
}
