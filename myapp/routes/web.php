<?php

use App\Http\Controllers\BranchCountController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BranchCountController::class, 'index'])->name('branch-count.index');
Route::post('/', [BranchCountController::class, 'store'])->name('branch-count.store');