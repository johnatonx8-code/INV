<?php

namespace App\Services;

use RuntimeException;

class InventoryCounter
{
    /** Read the first sheet of an uploaded .xlsx file and count units. */
    public function fromFile(string $path): array
    {
        return $this->fromRows((new XlsxReader)->rows($path));
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{asOf: string, total: int, name: string, list: array<int, array{0:string,1:string,2:int}>}
     */
    public function fromRows(array $rows): array
    {
        $header = null;
        $asOf = '';

        foreach ($rows as $i => $row) {
            $cells = array_map(fn ($c) => strtolower(trim((string) $c)), $row);

            if ($asOf === '' && str_starts_with($cells[0] ?? '', 'as of')) {
                $asOf = trim((string) $row[0]);
            }
            if (in_array('brand', $cells, true) && in_array('model', $cells, true)) {
                $header = $i;
                break;
            }
        }

        if ($header === null) {
            throw new RuntimeException('Could not find the Brand and Model columns. Check that this is an Ending Inventory export.');
        }

        $head = array_map(fn ($c) => strtolower(trim((string) $c)), $rows[$header]);
        $colBrand = array_search('brand', $head, true);
        $colModel = array_search('model', $head, true);
        $colOrigin = array_search('origin', $head, true);

        $counts = [];
        $origins = [];
        $total = 0;

        foreach (array_slice($rows, $header + 1) as $row) {
            $brand = trim((string) ($row[$colBrand] ?? ''));
            $model = $this->baseModel($row[$colModel] ?? '');
            if ($brand === '' || $model === '') {
                continue;
            }

            // Every row counts toward the one branch. The Origin column is not used to split the count.
            $key = $brand."\x01".$model;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
            $total++;

            $origin = $colOrigin === false ? '' : strtoupper(trim((string) ($row[$colOrigin] ?? '')));
            if ($origin !== '') {
                $origins[$origin] = ($origins[$origin] ?? 0) + 1;
            }
        }

        if ($total === 0) {
            throw new RuntimeException('The file has a header row but no units under it.');
        }

        $list = [];
        foreach ($counts as $key => $n) {
            [$brand, $model] = explode("\x01", $key);
            $list[] = [$brand, $model, $n];
        }
        usort($list, fn ($a, $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        // Suggested branch name: the most common Origin. The page lets you edit it.
        arsort($origins);
        $name = $origins ? (string) array_key_first($origins) : 'BRANCH';

        return ['asOf' => $asOf, 'total' => $total, 'name' => $name, 'list' => $list];
    }

    /** "CFT125MSS-BLACK" becomes "CFT125MSS". The color comes after the first hyphen. */
    public function baseModel(mixed $value): string
    {
        $value = trim((string) $value);
        $pos = strpos($value, '-');

        return trim($pos > 0 ? substr($value, 0, $pos) : $value);
    }
}
