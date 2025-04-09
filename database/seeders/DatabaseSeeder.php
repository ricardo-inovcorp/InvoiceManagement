<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Criar usuário admin
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        // Executar seeders na ordem correta (manter referências de integridade)
        $this->call([
            // Primeiro as tabelas de referência (sem dependências)
            SectorSeeder::class,
            OrganizationTypeSeeder::class,
            DistrictSeeder::class,
            
            // Depois as tabelas com dependências (distritos -> concelhos)
            CountySeeder::class,
            
            // Entidades principais
            SupplierSeeder::class,
            InvoiceSeeder::class,
            
            // Por último os itens de fatura (dependem das faturas)
            InvoiceItemSeeder::class,
        ]);
    }
}
