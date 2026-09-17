<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

Route::apiResource('authors', AuthorController::class);
Route::apiResource('books', BookController::class);

Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
Route::post('loans', [LoanController::class, 'store'])->name('loans.store');
Route::post('loans/{loan}/return', [LoanController::class, 'returnBook'])
    ->name('loans.return');
