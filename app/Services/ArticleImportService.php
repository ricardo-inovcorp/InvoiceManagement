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
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remover o cabeçalho (primeira linha)
            array_shift($rows);
            
            $this->results['total'] = count($rows);

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 porque começamos do 0 e pulamos o cabeçalho
                
                // Verificar se a linha tem dados
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    $this->processRow($row, $rowNumber);
                } catch (\Exception $e) {
                    $this->results['errors'][] = "Erro na linha {$rowNumber}: " . $e->getMessage();
                    Log::error("Erro na importação de artigo linha {$rowNumber}: " . $e->getMessage());
                }
            }

            return $this->results;
        } catch (\Exception $e) {
            Log::error("Erro na importação de artigos: " . $e->getMessage());
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
        $data = $this->mapRowToData($row);
        
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
            throw new \Exception($validator->errors()->first());
        }

        // Verificar se o artigo já existe
        if (Article::where('code', $data['code'])->exists()) {
            $this->results['duplicates']++;
            return;
        }

        // Processar categoria
        $categoryId = null;
        if (!empty($data['category'])) {
            $category = Category::firstOrCreate(
                ['name' => $data['category']],
                ['description' => '']
            );
            $categoryId = $category->id;
        }

        // Processar subcategoria
        $subcategoryId = null;
        if (!empty($data['subcategory']) && $categoryId) {
            $subcategory = Subcategory::firstOrCreate(
                [
                    'name' => $data['subcategory'],
                    'category_id' => $categoryId
                ],
                ['description' => '']
            );
            $subcategoryId = $subcategory->id;
        }

        // Criar o artigo
        Article::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? 0,
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'active' => true,
        ]);

        $this->results['imported']++;
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