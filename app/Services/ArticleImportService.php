<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ArticleImportService
{
    protected $results = [
        'total' => 0,
        'imported' => 0,
        'duplicates' => 0,
        'errors' => [],
    ];

    /**
     * Importa artigos de um arquivo Excel
     *
     * @param UploadedFile $file
     * @return array
     */
    public function import(UploadedFile $file): array
    {
        try {
            // Informações do arquivo para depuração
            Log::info("Iniciando importação", [
                'arquivo' => $file->getClientOriginalName(),
                'tamanho' => $file->getSize(),
                'tipo' => $file->getMimeType()
            ]);
            
            // Carregar o arquivo com verificação explícita
            $filePath = $file->getRealPath();
            if (!file_exists($filePath) || !is_readable($filePath)) {
                throw new \Exception("Arquivo não pode ser lido. Caminho: {$filePath}");
            }
            
            // Identificar o formato do arquivo com base na extensão
            $extension = strtolower($file->getClientOriginalExtension());
            Log::info("Extensão do arquivo: {$extension}");
            
            try {
                // Carregamos o spreadsheet com debug
                $spreadsheet = IOFactory::load($filePath);
                Log::info("Arquivo carregado com sucesso via IOFactory");
                
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                
                Log::info("Dados carregados do arquivo", [
                    'total_linhas' => count($rows),
                    'primeira_linha' => !empty($rows) ? json_encode(array_slice($rows, 0, 1)) : 'vazio'
                ]);
                
                // Remover o cabeçalho (primeira linha)
                if (count($rows) > 0) {
                    $header = array_shift($rows);
                    Log::info("Cabeçalho removido", ['conteudo' => json_encode($header)]);
                } else {
                    Log::warning("Arquivo sem linhas para importar");
                    $this->results['errors'][] = "O arquivo não contém dados para importar.";
                    return $this->results;
                }
                
                $this->results['total'] = count($rows);
                Log::info("Total de registros para processar: {$this->results['total']}");

                foreach ($rows as $index => $row) {
                    $rowNumber = $index + 2; // +2 porque começamos do 0 e pulamos o cabeçalho
                    
                    // Log da linha atual
                    Log::info("Processando linha {$rowNumber}", ['conteudo' => json_encode($row)]);
                    
                    // Verificar se a linha tem dados
                    if (empty(array_filter($row))) {
                        Log::info("Linha {$rowNumber} vazia, ignorando");
                        continue;
                    }

                    try {
                        $this->processRow($row, $rowNumber);
                    } catch (\Exception $e) {
                        $this->results['errors'][] = "Erro na linha {$rowNumber}: " . $e->getMessage();
                        Log::error("Erro na importação de artigo linha {$rowNumber}: " . $e->getMessage(), [
                            'linha' => json_encode($row),
                            'excecao' => get_class($e)
                        ]);
                    }
                }

                Log::info("Importação concluída", [
                    'importados' => $this->results['imported'],
                    'duplicados' => $this->results['duplicates'],
                    'erros' => count($this->results['errors'])
                ]);
                
                return $this->results;
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                Log::error("Erro no leitor de Excel: " . $e->getMessage(), [
                    'arquivo' => $file->getClientOriginalName(),
                    'excecao' => get_class($e)
                ]);
                throw new \Exception("Não foi possível abrir o arquivo. Verifique se o formato é suportado: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error("Erro na importação de artigos: " . $e->getMessage(), [
                'arquivo' => $file->getClientOriginalName(),
                'excecao' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Processa uma linha do Excel
     *
     * @param array $row
     * @param int $rowNumber
     * @return void
     */
    protected function processRow(array $row, int $rowNumber): void
    {
        // Log para depurar os valores da linha
        Log::info("Mapeando dados da linha {$rowNumber}", ['row_original' => json_encode($row)]);
        
        // Garantir que a linha tenha dados suficientes
        if (count($row) < 2) {
            throw new \Exception("A linha não contém dados suficientes (mínimo: código e nome)");
        }
        
        // Converter valores vazios para null
        $row = array_map(function ($value) {
            return $value === '' ? null : $value;
        }, $row);
        
        $data = $this->mapRowToData($row);
        Log::info("Dados mapeados da linha {$rowNumber}", ['data_mapeada' => json_encode($data)]);
        
        // Validar dados
        $validator = Validator::make($data, [
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'subcategory' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            Log::error("Validação falhou na linha {$rowNumber}", [
                'erros' => $errors,
                'dados' => $data
            ]);
            throw new \Exception(implode(', ', $errors));
        }

        // Verificar se o artigo já existe
        if (Article::where('code', $data['code'])->exists()) {
            Log::info("Artigo duplicado na linha {$rowNumber}", ['codigo' => $data['code']]);
            $this->results['duplicates']++;
            return;
        }

        // Processar categoria
        $categoryId = null;
        if (!empty($data['category'])) {
            try {
                $category = Category::firstOrCreate(
                    ['name' => $data['category']],
                    ['description' => '']
                );
                $categoryId = $category->id;
                Log::info("Categoria processada", [
                    'nome' => $data['category'],
                    'id' => $categoryId,
                    'nova' => $category->wasRecentlyCreated
                ]);
            } catch (\Exception $e) {
                Log::error("Erro ao processar categoria", [
                    'nome' => $data['category'],
                    'erro' => $e->getMessage()
                ]);
                throw new \Exception("Erro ao processar categoria '{$data['category']}': " . $e->getMessage());
            }
        }

        // Processar subcategoria
        $subcategoryId = null;
        if (!empty($data['subcategory']) && $categoryId) {
            try {
                $subcategory = Subcategory::firstOrCreate(
                    [
                        'name' => $data['subcategory'],
                        'category_id' => $categoryId
                    ],
                    ['description' => '']
                );
                $subcategoryId = $subcategory->id;
                Log::info("Subcategoria processada", [
                    'nome' => $data['subcategory'],
                    'categoria_id' => $categoryId,
                    'id' => $subcategoryId,
                    'nova' => $subcategory->wasRecentlyCreated
                ]);
            } catch (\Exception $e) {
                Log::error("Erro ao processar subcategoria", [
                    'nome' => $data['subcategory'],
                    'categoria_id' => $categoryId,
                    'erro' => $e->getMessage()
                ]);
                throw new \Exception("Erro ao processar subcategoria '{$data['subcategory']}': " . $e->getMessage());
            }
        }

        // Tratar o valor do preço
        $price = 0;
        if (isset($data['price'])) {
            if (is_string($data['price'])) {
                // Tratar número formatado (ex: "1.234,56")
                $price = str_replace(['.', ','], ['', '.'], $data['price']);
            } else {
                $price = $data['price'];
            }
        }
        
        // Criar o artigo
        try {
            $article = Article::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $price,
                'category_id' => $categoryId,
                'subcategory_id' => $subcategoryId,
                'active' => true,
            ]);
            
            Log::info("Artigo criado com sucesso", [
                'id' => $article->id,
                'codigo' => $article->code,
                'nome' => $article->name
            ]);
            
            $this->results['imported']++;
        } catch (\Exception $e) {
            Log::error("Erro ao criar artigo", [
                'codigo' => $data['code'],
                'nome' => $data['name'],
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception("Erro ao criar artigo '{$data['code']}': " . $e->getMessage());
        }
    }

    /**
     * Mapeia os dados da linha para o formato esperado
     *
     * @param array $row
     * @return array
     */
    protected function mapRowToData(array $row): array
    {
        // Colunas esperadas: Código, Nome, Descrição, Preço, Categoria, Subcategoria
        return [
            'code' => $row[0] ?? null,
            'name' => $row[1] ?? null,
            'description' => $row[2] ?? null,
            'price' => $row[3] ?? null,
            'category' => $row[4] ?? null,
            'subcategory' => $row[5] ?? null,
        ];
    }
} 