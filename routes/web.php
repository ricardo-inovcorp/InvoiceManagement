<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Rotas de Fornecedores
    Route::resource('suppliers', SupplierController::class);

    // Rotas de Faturas
    Route::resource('invoices', InvoiceController::class);

    // Rotas de Itens de Fatura
    Route::resource('invoices.items', InvoiceItemController::class)->shallow();
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
