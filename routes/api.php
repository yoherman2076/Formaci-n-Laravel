<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register'])->name('auth.register');
Route::post('login', [AuthController::class, 'login'])->name('auth.login');

Route::apiResource('authors', AuthorController::class);
Route::apiResource('books', BookController::class);

Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
Route::post('loans', [LoanController::class, 'store'])->name('loans.store');
Route::post('loans/{loan}/return', [LoanController::class, 'returnBook'])
    ->name('loans.return');

Route::middleware(['auth:sanctum', 'abilities:tickets:close'])->get('demo-close', function (): array {
    return ['ok' => true];
})->name('demo.close');

Route::middleware(['auth:sanctum', 'active', 'log.auth'])->group(function (): void {
    Route::get('me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
});

Route::middleware(['auth:sanctum', 'active', 'role:admin'])->prefix('admin')->group(function (): void {
    Route::get('ping', fn (): array => ['ok' => true])->name('admin.ping');
});
