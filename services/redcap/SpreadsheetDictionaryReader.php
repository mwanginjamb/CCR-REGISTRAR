<?php

namespace services\redcap;

use Generator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use RuntimeException;

final class SpreadsheetDictionaryReader
{
    public function read(string $file): Generator
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new RuntimeException("File is not readable: {$file}");
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        $reader = $extension === 'csv'
            ? $this->createCsvReader()
            : IOFactory::createReaderForFile($file);

        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($file);
        $worksheet = $spreadsheet->getActiveSheet();

        $headers = null;

        foreach ($worksheet->getRowIterator() as $row) {
            $rowNumber = $row->getRowIndex();
            $values = [];

            $iterator = $row->getCellIterator();
            $iterator->setIterateOnlyExistingCells(false);

            foreach ($iterator as $cell) {
                $value = $cell->getValue();
                $values[] = is_scalar($value) || $value === null
                    ? trim((string) $value)
                    : '';
            }

            if ($headers === null) {
                $headers = $this->normalizeHeaders($values);
                continue;
            }

            if ($this->isEmptyRow($values)) {
                continue;
            }

            $values = array_pad($values, count($headers), '');
            $values = array_slice($values, 0, count($headers));

            yield $rowNumber => array_combine($headers, $values);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    private function createCsvReader(): Csv
    {
        $reader = new Csv();
        $reader->setDelimiter(',');
        $reader->setEnclosure('"');
        $reader->setEscapeCharacter('\\');
        $reader->setInputEncoding('UTF-8');

        return $reader;
    }

    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $index => $header) {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);
            $header = strtolower(trim($header));
            $header = preg_replace('/[^a-z0-9]+/', '_', $header);
            $header = trim($header, '_');

            $normalized[] = $header !== ''
                ? $header
                : "column_{$index}";
        }

        if (count($normalized) !== count(array_unique($normalized))) {
            throw new RuntimeException('Duplicate normalized CSV headers detected.');
        }

        return $normalized;
    }

    private function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}