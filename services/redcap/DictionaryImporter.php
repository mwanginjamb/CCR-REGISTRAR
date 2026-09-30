<?php

namespace services\redcap;

use common\services\redcap\dto\DictionaryField;
use services\redcap\SpreadsheetDictionaryReader;
use Throwable;
use Yii;
use yii\db\Connection;

final class DictionaryImporter
{
    public function __construct(
        private readonly SpreadsheetDictionaryReader $reader,
        private readonly DictionaryRowParser $rowParser,
        private readonly ChoiceParser $choiceParser,
        private readonly IcdoChoiceExtractor $icdoExtractor,
        private readonly Connection $db,
    ) {
    }

    public function import(string $file, bool $force = false): array
    {
        $sourceHash = hash_file('sha256', $file);

        $existing = $this->db->createCommand(
            'SELECT id, status FROM {{%redcap_import_run}}
             WHERE source_hash = :hash'
        )
            ->bindValue(':hash', $sourceHash)
            ->queryOne();

        if ($existing && !$force) {
            return [
                'import_run_id' => (int) $existing['id'],
                'status' => 'skipped',
                'message' => 'This exact file has already been imported.',
            ];
        }

        if ($existing && $force) {
            $this->db->createCommand()
                ->delete('{{%redcap_import_run}}', [
                    'id' => (int) $existing['id'],
                ])
                ->execute();
        }

        $now = date('Y-m-d H:i:s');

        $this->db->createCommand()->insert('{{%redcap_import_run}}', [
            'source_name' => basename($file),
            'source_hash' => $sourceHash,
            'status' => 'pending',
            'started_at' => $now,
        ])->execute();

        $importRunId = (int) $this->db->getLastInsertID();
        $transaction = $this->db->beginTransaction();

        $rowCount = 0;
        $fieldCount = 0;
        $optionCount = 0;
        $warnings = [];

        try {
            foreach ($this->reader->read($file) as $rowNumber => $row) {
                $rowCount++;

                try {
                    $field = $this->rowParser->parse($rowNumber, $row);
                    $fieldId = $this->storeField($importRunId, $field);
                    $fieldCount++;

                    if (!$field->hasChoices()) {
                        continue;
                    }

                    $options = $this->choiceParser->parse($field->choicesText);
                    $optionCount += count($options);

                    if ($field->fieldName === 'primary_site_topography') {
                        $this->storeTopographies($options, $now);
                        continue;
                    }

                    if ($field->fieldName === 'histology_morphology') {
                        $this->storeMorphologies($options, $now);
                        continue;
                    }

                    $setId = $this->storeReferenceSet($field, $options, $now);
                    $this->storeReferenceOptions($setId, $options);
                    $this->linkFieldToSet($fieldId, $setId);
                } catch (Throwable $exception) {
                    $warnings[] = "Row {$rowNumber}: {$exception->getMessage()}";
                }
            }

            if ($fieldCount === 0) {
                throw new \RuntimeException(
                    'Import produced no dictionary fields.'
                );
            }

            $this->db->createCommand()->update(
                '{{%redcap_import_run}}',
                [
                    'status' => 'completed',
                    'row_count' => $rowCount,
                    'field_count' => $fieldCount,
                    'option_count' => $optionCount,
                    'warning_count' => count($warnings),
                    'completed_at' => date('Y-m-d H:i:s'),
                ],
                ['id' => $importRunId]
            )->execute();

            $transaction->commit();

            return [
                'import_run_id' => $importRunId,
                'status' => 'completed',
                'rows' => $rowCount,
                'fields' => $fieldCount,
                'options' => $optionCount,
                'warnings' => $warnings,
            ];
        } catch (Throwable $exception) {
            $transaction->rollBack();

            $this->db->createCommand()->update(
                '{{%redcap_import_run}}',
                [
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                    'completed_at' => date('Y-m-d H:i:s'),
                ],
                ['id' => $importRunId]
            )->execute();

            throw $exception;
        }
    }

    private function storeField(
        int $importRunId,
        DictionaryField $field
    ): int {
        $this->db->createCommand()->insert(
            '{{%redcap_dictionary_field}}',
            [
                'import_run_id' => $importRunId,
                'field_name' => $field->fieldName,
                'form_name' => $field->formName ?: null,
                'section_header' => $field->sectionHeader ?: null,
                'field_type' => $field->fieldType,
                'field_label' => $field->fieldLabel ?: null,
                'choices_text' => $field->choicesText ?: null,
                'field_note' => $field->fieldNote ?: null,
                'validation_type' => $field->validationType ?: null,
                'validation_min' => $field->validationMin ?: null,
                'validation_max' => $field->validationMax ?: null,
                'is_identifier' => $field->isIdentifier,
                'branching_logic' => $field->branchingLogic ?: null,
                'is_required' => $field->isRequired,
                'field_annotation' => $field->fieldAnnotation ?: null,
                'row_number' => $field->rowNumber,
                'row_hash' => $field->rowHash(),
                'raw_row_json' => json_encode(
                    $field->rawRow,
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                ),
            ]
        )->execute();

        return (int) $this->db->getLastInsertID();
    }

    private function storeReferenceSet(
        DictionaryField $field,
        array $options,
        string $now
    ): int {
        $fingerprint = $this->choiceParser->fingerprint($options);

        $id = $this->db->createCommand(
            'SELECT id FROM {{%reference_set}}
             WHERE fingerprint = :fingerprint'
        )
            ->bindValue(':fingerprint', $fingerprint)
            ->queryScalar();

        if ($id !== false) {
            return (int) $id;
        }

        $this->db->createCommand()->insert('{{%reference_set}}', [
            'name' => $field->fieldName,
            'label' => $field->fieldLabel ?: $field->fieldName,
            'source_type' => 'redcap',
            'fingerprint' => $fingerprint,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        return (int) $this->db->getLastInsertID();
    }

    private function storeReferenceOptions(int $setId, array $options): void
    {
        foreach ($options as $option) {
            $this->db->createCommand()->upsert(
                '{{%reference_option}}',
                [
                    'reference_set_id' => $setId,
                    'value' => $option['value'],
                    'label' => $option['label'],
                    'normalized_code' => null,
                    'sort_order' => $option['sort_order'],
                    'is_active' => true,
                ],
                [
                    'label' => $option['label'],
                    'sort_order' => $option['sort_order'],
                    'is_active' => true,
                ]
            )->execute();
        }
    }

    private function linkFieldToSet(int $fieldId, int $setId): void
    {
        $this->db->createCommand()->upsert(
            '{{%redcap_field_reference_set}}',
            [
                'field_id' => $fieldId,
                'reference_set_id' => $setId,
            ],
            false
        )->execute();
    }

    private function storeTopographies(array $options, string $now): void
    {
        foreach ($options as $option) {
            $item = $this->icdoExtractor->extractTopography($option);

            $this->db->createCommand()->upsert(
                '{{%icdo_topography}}',
                [
                    ...$item,
                    'source_version' => 'REDCap dictionary',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'redcap_value' => $item['redcap_value'],
                    'description' => $item['description'],
                    'is_active' => true,
                    'updated_at' => $now,
                ]
            )->execute();
        }
    }

    private function storeMorphologies(array $options, string $now): void
    {
        foreach ($options as $option) {
            $item = $this->icdoExtractor->extractMorphology($option);

            $this->db->createCommand()->upsert(
                '{{%icdo_morphology}}',
                [
                    ...$item,
                    'source_version' => 'REDCap dictionary',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'redcap_value' => $item['redcap_value'],
                    'description' => $item['description'],
                    'behaviour_code' => $item['behaviour_code'],
                    'is_active' => true,
                    'updated_at' => $now,
                ]
            )->execute();
        }
    }
}