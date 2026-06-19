<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetImportReader
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function readAssocRows(string $absolutePath): array
    {
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if ($extension === 'json') {
            $decoded = json_decode((string) file_get_contents($absolutePath), true);

            return is_array($decoded['rows'] ?? null) ? $decoded['rows'] : (is_array($decoded) ? $decoded : []);
        }

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            return $this->readExcelRows($absolutePath);
        }

        return $this->readCsvRows($absolutePath);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readCsvRows(string $absolutePath): array
    {
        $handle = fopen($absolutePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Unable to read import file.');
        }

        $header = fgetcsv($handle);

        if (!is_array($header)) {
            fclose($handle);

            return [];
        }

        $header = $this->normalizeHeader($header);
        $rows = [];

        while (($line = fgetcsv($handle)) !== false) {
            if ($line === [null] || $line === false) {
                continue;
            }

            $rows[] = $this->combineRow($header, $line);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readExcelRows(string $absolutePath): array
    {
        $spreadsheet = IOFactory::load($absolutePath);
        $sheetRows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if ($sheetRows === []) {
            return [];
        }

        $headerIndex = $this->detectHeaderRowIndex($sheetRows);
        $header = $this->normalizeHeader($sheetRows[$headerIndex] ?? []);
        $rows = [];

        foreach (array_slice($sheetRows, $headerIndex + 1) as $line) {
            if ($this->isEmptyLine($line)) {
                continue;
            }

            $rows[] = $this->combineRow($header, $line);
        }

        return $rows;
    }

    /**
     * @param  array<int, array<int, mixed>>  $sheetRows
     */
    private function detectHeaderRowIndex(array $sheetRows): int
    {
        $markers = [
            'نام',
            'name',
            'موبایل',
            'mobile',
            'نام مشتری',
            'کد ملی',
            'کدملی',
            'تابلو',
            'تاریخ خرید',
        ];

        foreach (array_slice($sheetRows, 0, 15) as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $matches = 0;

            foreach ($row as $cell) {
                $label = $this->normalizeHeaderLabel($cell);

                if ($label === '') {
                    continue;
                }

                foreach ($markers as $marker) {
                    if ($label === $marker || str_contains($label, $marker)) {
                        $matches++;
                        break;
                    }
                }
            }

            if ($matches >= 2) {
                return $index;
            }
        }

        return 0;
    }

    /**
     * @param  array<int, mixed>  $header
     * @param  array<int, mixed>  $line
     * @return array<string, mixed>
     */
    private function combineRow(array $header, array $line): array
    {
        $paddedLine = array_pad($line, count($header), null);
        $combined = @array_combine($header, $paddedLine);

        if (is_array($combined)) {
            return $combined;
        }

        $row = [];

        foreach ($header as $index => $column) {
            if ($column === '') {
                continue;
            }

            $row[$column] = $paddedLine[$index] ?? null;
        }

        return $row;
    }

    /**
     * @param  array<int, mixed>  $header
     * @return array<int, string>
     */
    private function normalizeHeader(array $header): array
    {
        return array_map(fn ($column) => $this->normalizeHeaderLabel($column), $header);
    }

    private function normalizeHeaderLabel(mixed $column): string
    {
        $label = trim((string) $column);
        $label = preg_replace('/^\xEF\xBB\xBF/u', '', $label) ?? $label;
        $label = str_replace("\xc2\xa0", ' ', $label);
        $label = preg_replace('/\s+/u', ' ', $label) ?? $label;

        return trim($label);
    }

    /**
     * @param  array<int, mixed>  $line
     */
    private function isEmptyLine(array $line): bool
    {
        foreach ($line as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
