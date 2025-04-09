<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verifica se já existe um admin para evitar duplicação
        if (User::where('email', 'admin@example.com')->exists()) {
            $this->command->info('O usuário admin já existe!');
            return;
        }

        // Cria o usuário admin
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => Hash::make('Admin123!'), // Senha segura de exemplo
            'email_verified_at' => now(),
        ]);

        $this->command->info('Usuário admin criado com sucesso!');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Senha: Admin123!');
    }
} 