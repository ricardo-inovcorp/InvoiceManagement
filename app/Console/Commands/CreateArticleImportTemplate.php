<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CreateArticleImportTemplate extends Command
{
    /**
     * O nome e a assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'make:article-template';

    /**
     * A descrição do comando.
     *
     * @var string
     */
    protected $description = 'Cria um template Excel para importação de artigos';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $this->info('Criando template de importação de artigos...');
        
        // Criar uma nova planilha
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Definir o título da planilha
        $sheet->setTitle('Importação de Artigos');
        
        // Configurar cabeçalhos
        $headers = ['Código', 'Nome', 'Descrição', 'Preço', 'Categoria', 'Subcategoria'];
        foreach ($headers as $index => $header) {
            $col = chr(65 + $index);  // A, B, C, ...
            $sheet->setCellValue("{$col}1", $header);
        }
        
        // Estilizar cabeçalhos
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        
        // Configurar largura das colunas
        $sheet->getColumnDimension('A')->setWidth(15);  // Código
        $sheet->getColumnDimension('B')->setWidth(30);  // Nome
        $sheet->getColumnDimension('C')->setWidth(40);  // Descrição
        $sheet->getColumnDimension('D')->setWidth(15);  // Preço
        $sheet->getColumnDimension('E')->setWidth(20);  // Categoria
        $sheet->getColumnDimension('F')->setWidth(20);  // Subcategoria
        
        // Adicionar dados de exemplo
        $exampleData = [
            ['ART001', 'Artigo de Exemplo 1', 'Descrição do artigo de exemplo 1', 29.99, 'Informática', 'Periféricos'],
            ['ART002', 'Artigo de Exemplo 2', 'Descrição do artigo de exemplo 2', 49.90, 'Informática', 'Componentes'],
            ['ART003', 'Artigo de Exemplo 3', 'Descrição do artigo de exemplo 3', 99.50, 'Escritório', 'Material de Escrita'],
        ];
        
        $rowIndex = 2;
        foreach ($exampleData as $row) {
            for ($i = 0; $i < count($row); $i++) {
                $col = chr(65 + $i);
                $sheet->setCellValue("{$col}{$rowIndex}", $row[$i]);
            }
            $rowIndex++;
        }
        
        // Estilizar células com dados de exemplo
        $dataCellStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        
        $sheet->getStyle('A2:F' . ($rowIndex - 1))->applyFromArray($dataCellStyle);
        
        // Adicionar uma linha de notas
        $noteRow = $rowIndex + 1;
        $sheet->setCellValue("A{$noteRow}", "NOTAS:");
        $sheet->mergeCells("A{$noteRow}:F{$noteRow}");
        $sheet->getStyle("A{$noteRow}")->getFont()->setBold(true);
        
        $notes = [
            "- O código deve ser único para cada artigo.",
            "- Nome é obrigatório.",
            "- Preço deve ser um valor numérico (utilizar ponto como separador decimal).",
            "- Se a categoria ou subcategoria não existir, será criada automaticamente.",
            "- Deixe as células sem preenchimento nos campos opcionais que não deseja definir.",
            "- Apague as linhas de exemplo antes de importar seus dados reais.",
        ];
        
        foreach ($notes as $index => $note) {
            $noteRowIndex = $noteRow + 1 + $index;
            $sheet->setCellValue("A{$noteRowIndex}", $note);
            $sheet->mergeCells("A{$noteRowIndex}:F{$noteRowIndex}");
        }
        
        // Adicionar filtros nas colunas
        $sheet->setAutoFilter("A1:F1");
        
        // Salvar o arquivo
        $path = public_path('templates/article-import-template.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        
        $this->info("Template criado com sucesso: {$path}");
        return Command::SUCCESS;
    }
} 