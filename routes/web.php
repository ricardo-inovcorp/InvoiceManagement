<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\API\NifController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

// Rota para consulta de NIF
Route::post('/api/nif/lookup', [NifController::class, 'lookup'])
    ->name('api.nif.lookup');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rotas de Fornecedores
    Route::resource('suppliers', SupplierController::class);

    // Rotas de Faturas
    Route::resource('invoices', InvoiceController::class);
    
    // Rota para visualizar arquivo da fatura
    Route::get('/view-invoice-file/{invoice}', function (App\Models\Invoice $invoice) {
        if (!$invoice->file_path || !Storage::exists($invoice->file_path)) {
            abort(404, 'Arquivo não encontrado');
        }
        
        return response()->file(Storage::path($invoice->file_path));
    })->name('invoices.view-file');

    // Rotas de Itens de Fatura
    Route::resource('invoices.items', InvoiceItemController::class)->shallow();
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
