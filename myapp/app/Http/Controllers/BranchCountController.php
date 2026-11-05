<?php

namespace App\Http\Controllers;

use App\Services\InventoryCounter;
use Illuminate\Http\Request;
use Throwable;

class BranchCountController

{
    public function index()
    {
        return view('branch-count.index', ['result' => null, 'error' => null]);
    }

    public function store(Request $request, InventoryCounter $counter)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:20480'],
        ]);

        try {
            // The upload is read from PHP's temp path and never saved or stored in the database.
           $result = $counter->fromFile($request->file('file')->getRealPath());
$result['name'] = strtoupper(pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME));
            $error = null;
        } catch (Throwable $e) {
            $result = null;
            $error = $e->getMessage();
        }

        return view('branch-count.index', compact('result', 'error'));
    }
}
