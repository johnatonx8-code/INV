<?php

namespace App\Services;

use RuntimeException;

/**
 * Minimal .xlsx reader. Needs only PHP's zlib and SimpleXML, so it works without ext-zip,
 * ext-gd or any Composer package. Reads the first sheet and returns rows of cell values.
 */
class XlsxReader
{
    private const REL_NS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /** @return array<int, array<int, string>> */
    public function rows(string $path): array
    {
        $files = $this->unzip($path);

        $shared = [];
        if (isset($files['xl/sharedStrings.xml'])) {
            $xml = $this->xml($files['xl/sharedStrings.xml']);
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $shared[] = (string) $si->t;
                } else {
                    $text = '';
                    foreach ($si->r as $run) {
                        $text .= (string) $run->t;
                    }
                    $shared[] = $text;
                }
            }
        }

        $sheetPath = $this->firstSheetPath($files);
        if (! isset($files[$sheetPath])) {
            throw new RuntimeException('Could not find a worksheet inside this file.');
        }

        $sheet = $this->xml($files[$sheetPath]);
        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $line = [];
            $next = 0;
            foreach ($row->c as $c) {
                $col = isset($c['r']) ? $this->columnIndex((string) $c['r']) : $next;
                $type = (string) $c['t'];

                if ($type === 's') {
                    $value = $shared[(int) $c->v] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = '';
                    if (isset($c->is->t)) {
                        $value = (string) $c->is->t;
                    } else {
                        foreach ($c->is->r as $run) {
                            $value .= (string) $run->t;
                        }
                    }
                } else {
                    $value = (string) $c->v;
                }

                $line[$col] = $value;
                $next = $col + 1;
            }

            if ($line) {
                $max = max(array_keys($line));
                $filled = [];
                for ($i = 0; $i <= $max; $i++) {
                    $filled[] = $line[$i] ?? '';
                }
                $rows[] = $filled;
            } else {
                $rows[] = [];
            }
        }

        return $rows;
    }

    private function firstSheetPath(array $files): string
    {
        if (isset($files['xl/workbook.xml'], $files['xl/_rels/workbook.xml.rels'])) {
            $wb = $this->xml($files['xl/workbook.xml']);
            $first = $wb->sheets->sheet[0] ?? null;
            if ($first !== null) {
                $rid = (string) $first->attributes(self::REL_NS)['id'];
                $rels = $this->xml($files['xl/_rels/workbook.xml.rels']);
                foreach ($rels->Relationship as $rel) {
                    if ((string) $rel['Id'] === $rid) {
                        $target = (string) $rel['Target'];

                        return str_starts_with($target, '/') ? ltrim($target, '/') : 'xl/'.$target;
                    }
                }
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private function xml(string $data): \SimpleXMLElement
    {
        $xml = @simplexml_load_string($data);
        if ($xml === false) {
            throw new RuntimeException('This file could not be read. Save it again as .xlsx and try once more.');
        }

        return $xml;
    }

    /** "AB12" -> 27 (zero-based column index). */
    private function columnIndex(string $ref): int
    {
        preg_match('/^[A-Z]+/i', $ref, $m);
        $n = 0;
        foreach (str_split(strtoupper($m[0] ?? 'A')) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }

        return $n - 1;
    }

    /** @return array<string, string> file name => contents */
    private function unzip(string $path): array
    {
        $data = @file_get_contents($path);
        if ($data === false || strlen($data) < 22) {
            throw new RuntimeException('The uploaded file could not be opened.');
        }
        if (! function_exists('gzinflate')) {
            throw new RuntimeException('PHP zlib extension is required to read .xlsx files.');
        }

        $eocd = strrpos($data, "PK\x05\x06");
        if ($eocd === false) {
            throw new RuntimeException('This is not a valid .xlsx file. Open it in Excel and save it as .xlsx.');
        }

        $end = unpack('vdisk/vcdDisk/ventriesDisk/ventries/Vsize/Voffset', substr($data, $eocd + 4, 18));
        $files = [];
        $pos = $end['offset'];

        for ($i = 0; $i < $end['entries']; $i++) {
            if (substr($data, $pos, 4) !== "PK\x01\x02") {
                break;
            }
            $h = unpack('vmade/vneed/vflags/vmethod/vtime/vdate/Vcrc/Vcsize/Vsize/vnlen/velen/vclen/vdisk/vint/Vext/Voff', substr($data, $pos + 4, 42));
            $name = substr($data, $pos + 46, $h['nlen']);
            $pos += 46 + $h['nlen'] + $h['elen'] + $h['clen'];

            if (str_ends_with($name, '/')) {
                continue;
            }

            $local = unpack('vnlen/velen', substr($data, $h['off'] + 26, 4));
            $start = $h['off'] + 30 + $local['nlen'] + $local['elen'];
            $raw = substr($data, $start, $h['csize']);

            if ($h['method'] === 0) {
                $files[$name] = $raw;
            } elseif ($h['method'] === 8) {
                $out = @gzinflate($raw);
                if ($out !== false) {
                    $files[$name] = $out;
                }
            }
        }

        return $files;
    }
}
