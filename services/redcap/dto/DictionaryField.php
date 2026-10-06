<?php

namespace app\services\redcap\dto;

final readonly class DictionaryField
{
    public function __construct(
        public int $rowNumber,
        public string $fieldName,
        public string $formName,
        public string $sectionHeader,
        public string $fieldType,
        public string $fieldLabel,
        public string $choicesText,
        public string $fieldNote,
        public string $validationType,
        public string $validationMin,
        public string $validationMax,
        public bool $isIdentifier,
        public string $branchingLogic,
        public bool $isRequired,
        public string $fieldAnnotation,
        public array $rawRow,
    ) {
    }

    public function hasChoices(): bool
    {
        return in_array(
            $this->fieldType,
            ['dropdown', 'radio', 'checkbox', 'yesno'],
            true
        ) && trim($this->choicesText) !== '';
    }

    public function rowHash(): string
    {
        return hash(
            'sha256',
            json_encode($this->rawRow, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        );
    }
}