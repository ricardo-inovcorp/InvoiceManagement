<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Subcategory;
use App\Services\ArticleImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ArticleController extends Controller
{
    /**
     * Exibe uma listagem de artigos.
     */
    public function index(Request $request)
    {
        $articles = Article::with(['category', 'subcategory'])
            ->when($request->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, function($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->subcategory_id, function($query, $subcategoryId) {
                $query->where('subcategory_id', $subcategoryId);
            })
            ->when($request->has('active'), function($query) use ($request) {
                $query->where('active', $request->active);
            })
            ->orderBy($request->input('sort_by', 'name'), $request->input('sort_order', 'asc'))
            ->paginate($request->input('per_page', 10))
            ->withQueryString();

        $categories = Category::all();

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'subcategory_id', 'active', 'sort_by', 'sort_order', 'per_page']),
        ]);
    }

    /**
     * Exibe o formulário para criar um novo artigo.
     */
    public function create()
    {
        $categories = Category::all();
        
        return Inertia::render('Articles/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Armazena um artigo recém-criado.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:articles',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'active' => 'boolean',
        ]);

        Article::create($validated);

        return redirect()->route('articles.index')
            ->with('success', 'Artigo criado com sucesso.');
    }

    /**
     * Exibe um artigo específico.
     */
    public function show(Article $article)
    {
        $article->load(['category', 'subcategory']);
        
        return Inertia::render('Articles/Show', [
            'article' => $article,
        ]);
    }

    /**
     * Exibe o formulário para editar um artigo.
     */
    public function edit(Article $article)
    {
        $article->load(['category', 'subcategory']);
        $categories = Category::all();
        $subcategories = Subcategory::where('category_id', $article->category_id)->get();
        
        return Inertia::render('Articles/Edit', [
            'article' => $article,
            'categories' => $categories,
            'subcategories' => $subcategories,
        ]);
    }

    /**
     * Atualiza um artigo específico.
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:articles,code,' . $article->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'active' => 'boolean',
        ]);

        // Certificar-se de que o valor de 'active' é um booleano verdadeiro
        $validated['active'] = (bool) $validated['active'];
        
        // Debugar o valor que estamos salvando
        Log::info('Atualizando artigo ' . $article->id . ' com dados: ', $validated);
        
        $article->update($validated);

        return redirect()->route('articles.index')
            ->with('success', 'Artigo atualizado com sucesso.');
    }

    /**
     * Exibe a página de importação de artigos.
     */
    public function showImport()
    {
        return Inertia::render('Articles/Import');
    }

    /**
     * Processa a importação de artigos.
     */
    public function import(Request $request, ArticleImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB max
        ]);

        try {
            $results = $importService->import($request->file('file'));
            
            return back()->with([
                'success' => 'Importação concluída com sucesso.',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Erro ao processar o arquivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Download do template de importação de artigos.
     */
    public function downloadTemplate()
    {
        $templatePath = public_path('templates/article-import-template.xlsx');
        
        // Verificar se o arquivo existe
        if (!file_exists($templatePath)) {
            return back()->withErrors(['message' => 'Template não encontrado.']);
        }
        
        return response()->download($templatePath, 'template-importacao-artigos.xlsx');
    }

    /**
     * Obtém subcategorias para uma categoria específica.
     */
    public function subcategories(Category $category)
    {
        $subcategories = $category->subcategories;
        
        return response()->json($subcategories);
    }
} 