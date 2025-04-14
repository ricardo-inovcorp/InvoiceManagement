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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            ->orderBy($request->input('sort_by', 'code'), $request->input('sort_order', 'asc'))
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
        try {
            // Primeiro validamos o arquivo
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB max
            ], [
                'file.required' => 'Por favor, selecione um arquivo para importar.',
                'file.file' => 'O ficheiro enviado é inválido.',
                'file.mimes' => 'O ficheiro deve ser do tipo Excel (xlsx, xls) ou CSV.',
                'file.max' => 'O ficheiro não pode ter mais de 5MB.',
            ]);

            // Verificar se o arquivo existe e pode ser lido
            $file = $request->file('file');
            if (!$file || !$file->isValid()) {
                throw new \Exception('O arquivo não pôde ser processado. Verifique se o formato é suportado.');
            }

            // Processar a importação
            $results = $importService->import($file);
            
            // Log detalhado dos resultados
            Log::info('Resultados da importação de artigos:', $results);
            
            // Criamos uma mensagem de resumo detalhada para o usuário
            if ($results['imported'] > 0) {
                $message = "Importação concluída com sucesso: {$results['imported']} artigos importados, {$results['duplicates']} ignorados" . 
                    (count($results['errors']) > 0 ? ", " . count($results['errors']) . " erros encontrados." : ".");
            } else if (count($results['errors']) > 0) {
                $message = "Importação finalizada com problemas: {$results['imported']} artigos importados, {$results['duplicates']} ignorados, " . 
                    count($results['errors']) . " erros encontrados.";
            } else if ($results['duplicates'] > 0) {
                $message = "Importação finalizada: Todos os {$results['duplicates']} artigos já existiam no sistema (ignorados).";
            } else {
                $message = "Nenhum artigo importado. Verifique o arquivo e tente novamente.";
            }
            
            // Log adicional para debug
            Log::info('Mensagem para o usuário:', ['message' => $message]);
            
            // Retornar os resultados e a mensagem usando withFlash
            return redirect()->back()->with([
                'success' => $message,
                'results' => $results
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Erros de validação são retornados automaticamente pelo Laravel
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            // Log detalhado do erro para diagnóstico
            Log::error('Erro na importação de artigos: ' . $e->getMessage(), [
                'file' => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : 'Nenhum arquivo',
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Mensagem amigável para o usuário
            return back()->withErrors(['file' => 'Erro ao processar o arquivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Download do template de importação de artigos.
     */
    public function downloadTemplate()
    {
        try {
            $templatePath = public_path('templates/article-import-template.xlsx');
            
            // Verificar se o arquivo existe
            if (!file_exists($templatePath)) {
                // Se não existir, criar um template básico
                Log::warning('Template de importação não encontrado. Criando um novo.', ['caminho' => $templatePath]);
                return $this->createAndDownloadTemplate();
            }
            
            return response()->download($templatePath, 'template-importacao-artigos.xlsx');
        } catch (\Exception $e) {
            Log::error('Erro ao baixar template:', ['erro' => $e->getMessage()]);
            return back()->withErrors(['message' => 'Não foi possível baixar o template: ' . $e->getMessage()]);
        }
    }

    /**
     * Cria um template básico para importação de artigos
     */
    private function createAndDownloadTemplate()
    {
        // Usar a biblioteca PhpSpreadsheet para criar um template
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Definir cabeçalhos
        $headers = ['Código', 'Nome', 'Descrição', 'Preço', 'Categoria', 'Subcategoria'];
        $sheet->fromArray([$headers], null, 'A1');
        
        // Adicionar exemplo
        $example = ['ART001', 'Produto de Exemplo', 'Descrição do produto', '100.00', 'Categoria Exemplo', 'Subcategoria Exemplo'];
        $sheet->fromArray([$example], null, 'A2');
        
        // Estilizar cabeçalhos
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        
        // Ajustar largura das colunas
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Criar diretório para armazenar o template se não existir
        $templateDir = public_path('templates');
        if (!file_exists($templateDir)) {
            mkdir($templateDir, 0755, true);
        }
        
        // Salvar o arquivo
        $templatePath = public_path('templates/article-import-template.xlsx');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($templatePath);
        
        Log::info('Template de importação criado com sucesso', ['caminho' => $templatePath]);
        
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