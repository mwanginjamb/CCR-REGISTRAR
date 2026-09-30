<?php

namespace services\redcap;

use RuntimeException;

final class ChoiceParser
{
    public function parse(string $choices): array
    {
        $choices = trim($choices);

        if ($choices === '') {
            return [];
        }

        $options = [];
        $seen = [];

        foreach (preg_split('/\s*\|\s*/u', $choices) as $index => $item) {
            $item = trim($item);

            if ($item === '') {
                continue;
            }

            $parts = preg_split('/\s*,\s*/u', $item, 2);

            if (count($parts) !== 2) {
                throw new RuntimeException(
                    "Invalid REDCap choice at position {$index}: {$item}"
                );
            }

            [$value, $label] = $parts;
            $value = trim($value);
            $label = trim($label);

            if ($value === '' || $label === '') {
                throw new RuntimeException("Empty choice value or label: {$item}");
            }

            if (isset($seen[$value])) {
                throw new RuntimeException(
                    "Duplicate REDCap choice value: {$value}"
                );
            }

            $seen[$value] = true;

            $options[] = [
                'value' => $value,
                'label' => $label,
                'sort_order' => count($options) + 1,
            ];
        }

        return $options;
    }

    public function fingerprint(array $options): string
    {
        $canonical = array_map(
            static fn(array $option): array => [
                'value' => (string) $option['value'],
                'label' => preg_replace(
                    '/\s+/u',
                    ' ',
                    trim((string) $option['label'])
                ),
            ],
            $options
        );

        return hash(
            'sha256',
            json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        );
    }
}