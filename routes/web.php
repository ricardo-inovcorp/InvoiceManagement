<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rotas de Fornecedores
    Route::resource('suppliers', SupplierController::class);

    // Rotas de Faturas
    Route::resource('invoices', InvoiceController::class);

    // Rotas de Itens de Fatura
    Route::resource('invoices.items', InvoiceItemController::class)->shallow();
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
