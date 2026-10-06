<?php

namespace app\services\redcap;

use app\services\redcap\dto\DictionaryField;
use RuntimeException;

final class DictionaryRowParser
{
    public function parse(int $rowNumber, array $row): DictionaryField
    {
        $fieldName = $this->get($row, 'variable_field_name');

        if ($fieldName === '') {
            throw new RuntimeException(
                "Missing Variable / Field Name on row {$rowNumber}."
            );
        }

        return new DictionaryField(
            rowNumber: $rowNumber,
            fieldName: $fieldName,
            formName: $this->get($row, 'form_name'),
            sectionHeader: $this->get($row, 'section_header'),
            fieldType: strtolower($this->get($row, 'field_type')),
            fieldLabel: $this->get($row, 'field_label'),
            choicesText: $this->get(
                $row,
                'choices_calculations_or_slider_labels'
            ),
            fieldNote: $this->get($row, 'field_note'),
            validationType: $this->get(
                $row,
                'text_validation_type_or_show_slider_number'
            ),
            validationMin: $this->get($row, 'text_validation_min'),
            validationMax: $this->get($row, 'text_validation_max'),
            isIdentifier: $this->isYes($this->get($row, 'identifier')),
            branchingLogic: $this->get(
                $row,
                'branching_logic_show_field_only_if'
            ),
            isRequired: $this->isYes($this->get($row, 'required_field')),
            fieldAnnotation: $this->get($row, 'field_annotation'),
            rawRow: $row
        );
    }

    private function get(array $row, string $key): string
    {
        return trim((string) ($row[$key] ?? ''));
    }

    private function isYes(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['y', 'yes', '1'], true);
    }
}