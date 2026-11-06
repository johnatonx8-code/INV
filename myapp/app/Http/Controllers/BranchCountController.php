<?php

namespace App\Http\Controllers;

use App\Services\InventoryCounter;
use Illuminate\Http\Request;
use Throwable;

class BranchCountController
{
    public function index()
    {
        return view('branch-count.index');
    }

    /**
     * Counts the uploaded files and returns the branch cards as HTML.
     * The page adds them to the branches already on screen, so nothing is stored here.
     */
    public function store(Request $request, InventoryCounter $counter)
    {
        $request->validate([
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['file', 'extensions:xlsx', 'max:20480'],
        ], [
            'files.required' => 'Choose at least one .xlsx file. If you did, the files together may be larger than PHP allows (8 MB by default).',
            'files.max' => 'You can add up to 20 files at a time.',
            'files.*.extensions' => 'Only .xlsx files are accepted.',
            'files.*.max' => 'Each file must be 20 MB or smaller.',
        ]);

        $html = '';
        $problems = [];
        $added = 0;

        foreach ($request->file('files') as $file) {
            $label = $file->getClientOriginalName();

            try {
                // Each upload is read from PHP's temp path and never saved or stored in the database.
                $result = $counter->fromFile($file->getRealPath());

                $html .= view('branch-count._card', ['b' => [
                    // The file name is the branch name: "Marilao.xlsx" becomes MARILAO.
                    'name' => strtoupper(pathinfo($label, PATHINFO_FILENAME)),
                    'asOf' => $result['asOf'],
                    'total' => $result['total'],
                    'list' => $result['list'],
                ]])->render();
                $added++;
            } catch (Throwable $e) {
                $problems[] = $label.': '.$e->getMessage();
            }
        }

        return response()->json(['html' => $html, 'added' => $added, 'problems' => $problems]);
    }
}
