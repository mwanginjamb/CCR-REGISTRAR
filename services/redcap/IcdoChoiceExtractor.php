<?php

namespace services\redcap;

use RuntimeException;

final class IcdoChoiceExtractor
{
    public function extractTopography(array $option): array
    {
        $label = trim($option['label']);

        if (
            !preg_match(
                '/\b(C\d{2}(?:\.\d)?)\b\s*(.*)$/iu',
                $label,
                $matches
            )
        ) {
            throw new RuntimeException(
                "Cannot extract topography code from: {$label}"
            );
        }

        return [
            'redcap_value' => (string) $option['value'],
            'code' => strtoupper($matches[1]),
            'description' => trim($matches[2]),
        ];
    }

    public function extractMorphology(array $option): array
    {
        $label = trim($option['label']);
        $redcapValue = trim((string) $option['value']);

        if (!preg_match('/^\d{4}$/', $redcapValue)) {
            throw new RuntimeException(
                "Invalid morphology value: {$redcapValue}"
            );
        }

        $description = preg_replace(
            '/\s*\(' . preg_quote($redcapValue, '/') . '(?:\/\d)?\)\s*$/u',
            '',
            $label
        );

        $behaviour = null;

        if (
            preg_match(
                '/\(' . preg_quote($redcapValue, '/') . '\/([0-9])\)\s*$/u',
                $label,
                $matches
            )
        ) {
            $behaviour = $matches[1];
        }

        return [
            'redcap_value' => $redcapValue,
            'code' => $redcapValue,
            'description' => trim($description),
            'behaviour_code' => $behaviour,
        ];
    }
}