<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obter todos os fornecedores
        $suppliers = Supplier::all();

        if ($suppliers->count() === 0) {
            // Se não houver fornecedores, criar alguns
            $suppliers = Supplier::factory()->count(5)->create();
        }

        // Para cada fornecedor, criar de 1 a 5 faturas
        foreach ($suppliers as $supplier) {
            $numInvoices = rand(1, 5);
            
            // Criar faturas aleatórias
            Invoice::factory()
                ->count($numInvoices)
                ->for($supplier)
                ->create();
                
            // Criar pelo menos 1 fatura paga
            Invoice::factory()
                ->paid()
                ->for($supplier)
                ->create();
                
            // 30% de chance de ter uma fatura em atraso
            if (rand(1, 100) <= 30) {
                Invoice::factory()
                    ->overdue()
                    ->for($supplier)
                    ->create();
            }
        }
    }
}
