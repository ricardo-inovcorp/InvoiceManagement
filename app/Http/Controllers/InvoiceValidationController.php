<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Article;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Schema;

class InvoiceValidationController extends Controller
{
    /**
     * Exibe a tela de validação de fatura
     */
    public function show(Invoice $invoice): Response
    {
        // Carrega a fatura com seus itens, fornecedor e artigos relacionados
        $invoice->load(['supplier', 'items', 'items.article']);
        
        // Busca todos os artigos ativos para a associação
        $articles = Article::where('active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'price']);
            
        // Busca todas as categorias para o formulário de criação de artigos
        // Se a tabela não existir ou estiver vazia, retorna um array vazio
        try {
            $categories = [];
            
            // Verificar se a tabela existe
            if (Schema::hasTable('categories')) {
                $categories = \App\Models\Category::orderBy('name')->get(['id', 'name'])->toArray();
            }
        } catch (\Exception $e) {
            // Em caso de erro, simplesmente continua com um array vazio
            $categories = [];
        }
        
        return Inertia::render('Invoices/Validate', [
            'invoice' => $invoice,
            'articles' => $articles,
            'categories' => $categories,
            'invalidItemsCount' => $invoice->getInvalidItems()->count()
        ]);
    }
    
    /**
     * Associa um artigo a um item da fatura
     */
    public function associateArticle(Request $request, InvoiceItem $item)
    {
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
        ]);
        
        DB::beginTransaction();
        try {
            $article = Article::findOrFail($validated['article_id']);
            
            // Associa o artigo ao item
            $item->article_id = $article->id;
            $item->is_valid = true;
            $item->save();
            
            // Registra o log de atividade
            $this->logActivity($item->invoice, 'validated_item', 
                "Item {$item->id} associado ao artigo {$article->code}");
            
            DB::commit();
            
            // Recarrega a fatura com seus relacionamentos
            $invoice = $item->invoice->fresh(['supplier', 'items', 'items.article']);
            
            // Retorna uma resposta Inertia redirecionando para a mesma página
            return Inertia::location(route('invoices.validate', $invoice->id));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao associar artigo: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Cria um novo artigo e o associa ao item
     */
    public function createAndAssociateArticle(Request $request, InvoiceItem $item)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:articles',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
        ]);
        
        DB::beginTransaction();
        try {
            // Cria o novo artigo
            $article = Article::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'price' => $validated['price'],
                'category_id' => $validated['category_id'] ?? null,
                'subcategory_id' => $validated['subcategory_id'] ?? null,
                'active' => true,
            ]);
            
            // Associa o artigo ao item
            $item->article_id = $article->id;
            $item->is_valid = true;
            $item->save();
            
            // Registra o log de atividade
            $this->logActivity($item->invoice, 'created_article_and_validated', 
                "Criado artigo {$article->code} e associado ao item {$item->id}");
            
            DB::commit();
            
            // Recarrega a fatura com seus relacionamentos
            $invoice = $item->invoice->fresh(['supplier', 'items', 'items.article']);
            
            // Retorna uma resposta Inertia redirecionando para a mesma página
            return Inertia::location(route('invoices.validate', $invoice->id));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao criar artigo: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Marca a fatura como validada (todos os itens associados)
     */
    public function markAsValidated(Invoice $invoice)
    {
        if (!$invoice->allItemsValidated()) {
            return redirect()->back()->withErrors(['error' => 'Todos os itens devem estar associados a artigos para validar a fatura.']);
        }
        
        DB::beginTransaction();
        try {
            $invoice->validation_status = Invoice::VALIDATION_VALIDATED;
            $invoice->save();
            
            // Registra o log de atividade
            $this->logActivity($invoice, 'validated', 'Fatura marcada como validada.');
            
            DB::commit();
            
            // Redirecionamento padrão para a tela de detalhes da fatura
            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Fatura validada com sucesso.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao validar fatura: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Marca a fatura como verificada (pronta para pagamento)
     */
    public function markAsVerified(Invoice $invoice)
    {
        if ($invoice->validation_status !== Invoice::VALIDATION_VALIDATED) {
            return redirect()->back()->withErrors(['error' => 'A fatura deve estar validada antes de ser verificada.']);
        }
        
        DB::beginTransaction();
        try {
            $invoice->validation_status = Invoice::VALIDATION_VERIFIED;
            $invoice->save();
            
            // Registra o log de atividade
            $this->logActivity($invoice, 'verified', 'Fatura marcada como verificada.');
            
            DB::commit();
            
            // Redirecionamento padrão para a tela de detalhes da fatura
            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Fatura verificada com sucesso.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao verificar fatura: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Registra log de atividade na fatura
     */
    private function logActivity(Invoice $invoice, string $action, string $description)
    {
        $invoice->logs()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'old_values' => null,
            'new_values' => null,
        ]);
    }
}
