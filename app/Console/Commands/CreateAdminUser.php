<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {email?} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criar um usuário administrador para o sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('Qual o email do administrador?', 'admin@example.com');
        $password = $this->argument('password') ?? $this->secret('Qual a senha? (mínimo 8 caracteres)');

        if (strlen($password) < 8) {
            $this->error('A senha deve ter no mínimo 8 caracteres!');
            return 1;
        }

        // Verifica se o usuário já existe
        $exists = User::where('email', $email)->exists();
        
        if ($exists) {
            // Atualiza a senha do usuário existente
            $user = User::where('email', $email)->first();
            $user->password = Hash::make($password);
            $user->save();
            
            $this->info("Usuário administrador atualizado com sucesso!");
        } else {
            // Cria um novo usuário
            User::create([
                'name' => 'Administrador',
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            
            $this->info("Usuário administrador criado com sucesso!");
        }
        
        $this->info("Email: $email");
        $this->info("Use a URL http://127.0.0.1:8000/login para acessar o sistema");
        
        return 0;
    }
}
