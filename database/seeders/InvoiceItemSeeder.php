<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoiceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obter todas as faturas
        $invoices = Invoice::all();
        
        if ($invoices->count() === 0) {
            return; // Se não houver faturas, não criar itens
        }
        
        // Para cada fatura, criar entre 1 e 6 itens
        foreach ($invoices as $invoice) {
            $numItems = rand(1, 6);
            
            // Criar os itens para esta fatura
            InvoiceItem::factory()
                ->count($numItems)
                ->for($invoice)
                ->create();
                
            // Atualizar os valores totais da fatura
            $this->updateInvoiceTotals($invoice);
        }
    }
    
    /**
     * Atualiza os totais da fatura com base nos itens
     */
    private function updateInvoiceTotals(Invoice $invoice): void
    {
        // Calcular os totais
        $baseAmount = $invoice->items->sum('total_price');
        $taxAmount = $invoice->items->sum('tax_amount');
        $totalAmount = $baseAmount + $taxAmount;
        
        // Atualizar a fatura
        $invoice->update([
            'base_amount' => $baseAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount
        ]);
    }
}
