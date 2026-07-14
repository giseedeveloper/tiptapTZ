<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Minimal XLSX writer (Office Open XML) with no external Excel dependencies.
 */
class SimpleXlsxWriter
{
    /**
     * @param  list<array{title: string, rows: list<list<string|int|float|null>>}>  $sheets
     */
    public function write(string $absolutePath, array $sheets): void
    {
        $dir = dirname($absolutePath);
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new RuntimeException('Unable to create directory for Excel export: '.$dir);
        }

        $zip = new ZipArchive;
        if ($zip->open($absolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create Excel file: '.$absolutePath);
        }

        $sheetCount = max(1, count($sheets));
        $sheetFiles = [];
        $workbookSheets = [];

        foreach (array_values($sheets) as $index => $sheet) {
            $sheetId = $index + 1;
            $sheetName = $this->sanitizeSheetName((string) ($sheet['title'] ?? 'Sheet'.$sheetId));
            $sheetPath = 'xl/worksheets/sheet'.$sheetId.'.xml';
            $sheetFiles[$sheetPath] = $this->worksheetXml($sheet['rows'] ?? []);
            $workbookSheets[] = '<sheet name="'.$this->xml($sheetName).'" sheetId="'.$sheetId.'" r:id="rId'.$sheetId.'"/>';
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml($sheetCount));
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml($workbookSheets));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml($sheetCount));
        $zip->addFromString('xl/styles.xml', $this->stylesXml());

        foreach ($sheetFiles as $path => $xml) {
            $zip->addFromString($path, $xml);
        }

        $zip->close();
    }

    /**
     * @param  list<list<string|int|float|null>>  $rows
     */
    private function worksheetXml(array $rows): string
    {
        $xmlRows = [];
        foreach (array_values($rows) as $rIndex => $row) {
            $rowNum = $rIndex + 1;
            $cells = [];
            foreach (array_values($row) as $cIndex => $value) {
                $ref = $this->cellRef($cIndex, $rowNum);
                if (is_int($value) || is_float($value)) {
                    $cells[] = '<c r="'.$ref.'"><v>'.$value.'</v></c>';
                } else {
                    $text = $this->xml((string) ($value ?? ''));
                    $cells[] = '<c r="'.$ref.'" t="inlineStr"><is><t>'.$text.'</t></is></c>';
                }
            }
            $xmlRows[] = '<row r="'.$rowNum.'">'.implode('', $cells).'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            .' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheetData>'.implode('', $xmlRows).'</sheetData>'
            .'</worksheet>';
    }

    private function cellRef(int $columnIndex, int $rowNumber): string
    {
        $column = '';
        $index = $columnIndex;
        do {
            $column = chr(65 + ($index % 26)).$column;
            $index = intdiv($index, 26) - 1;
        } while ($index >= 0);

        return $column.$rowNumber;
    }

    private function sanitizeSheetName(string $name): string
    {
        $clean = preg_replace('/[\\\\\/\\?\\*\\[\\]:]/', '', $name) ?: 'Sheet';

        return mb_substr($clean, 0, 31);
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function contentTypesXml(int $sheetCount): string
    {
        $overrides = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet'.$i.'.xml" '
                .'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .$overrides
            .'</Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    /**
     * @param  list<string>  $workbookSheets
     */
    private function workbookXml(array $workbookSheets): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            .' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'.implode('', $workbookSheets).'</sheets>'
            .'</workbook>';
    }

    private function workbookRelsXml(int $sheetCount): string
    {
        $rels = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $rels .= '<Relationship Id="rId'.$i.'" '
                .'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" '
                .'Target="worksheets/sheet'.$i.'.xml"/>';
        }
        $rels .= '<Relationship Id="rId'.($sheetCount + 1).'" '
            .'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" '
            .'Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$rels
            .'</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            .'<borders count="1"><border/></borders>'
            .'<cellStyleXfs count="1"><xf/></cellStyleXfs>'
            .'<cellXfs count="1"><xf/></cellXfs>'
            .'</styleSheet>';
    }
}
