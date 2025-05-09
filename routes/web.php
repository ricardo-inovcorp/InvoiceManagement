<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\API\NifController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\InvoiceValidationController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankTransactionController;

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
    
    // Rotas de Validação de Faturas
    Route::get('/invoices/{invoice}/validate', [InvoiceValidationController::class, 'show'])
        ->name('invoices.validate');
    Route::post('/invoice-items/{item}/associate-article', [InvoiceValidationController::class, 'associateArticle'])
        ->name('invoice-items.associate-article');
    Route::post('/invoice-items/{item}/create-and-associate', [InvoiceValidationController::class, 'createAndAssociateArticle'])
        ->name('invoice-items.create-and-associate');
    Route::post('/invoices/{invoice}/mark-validated', [InvoiceValidationController::class, 'markAsValidated'])
        ->name('invoices.mark-validated');
    Route::post('/invoices/{invoice}/mark-verified', [InvoiceValidationController::class, 'markAsVerified'])
        ->name('invoices.mark-verified');
    
    // Rota para visualizar arquivo da fatura
    Route::get('/view-invoice-file/{invoice}', function (App\Models\Invoice $invoice) {
        if (!$invoice->file_path || !Storage::exists($invoice->file_path)) {
            abort(404, 'Arquivo não encontrado');
        }
        
        return response()->file(Storage::path($invoice->file_path));
    })->name('invoices.view-file');

    // Rotas de Itens de Fatura
    Route::resource('invoices.items', InvoiceItemController::class)->shallow();
    
    // Rotas de Artigos
    Route::resource('articles', ArticleController::class);
    
    // Rotas de Importação de Artigos
    Route::get('/articles-import', [ArticleController::class, 'showImport'])->name('articles.import.show');
    Route::post('/articles-import', [ArticleController::class, 'import'])->name('articles.import');
    Route::get('/articles-import/template', [ArticleController::class, 'downloadTemplate'])->name('articles.import.template');
    
    // Rota para obter subcategorias de uma categoria
    Route::get('/categories/{category}/subcategories', [ArticleController::class, 'subcategories'])->name('categories.subcategories');
    
    // Rotas para gerenciar categorias e subcategorias
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/subcategories', [SubcategoryController::class, 'store'])->name('subcategories.store');

    // Rotas para contas bancárias
    Route::resource('bank-accounts', BankAccountController::class);

    // Rotas para transações bancárias
    Route::resource('bank-transactions', BankTransactionController::class);

    // Exportação de transações
    Route::get('/bank-accounts/{bankAccount}/export', [BankAccountController::class, 'export'])
        ->name('bank-accounts.export');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
