<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar 20 fornecedores usando a factory
        Supplier::factory()->count(20)->create();
        
        // Criar alguns fornecedores específicos com estados definidos
        // 5 fornecedores inativos
        Supplier::factory()->count(5)->inactive()->create();
    }
}
