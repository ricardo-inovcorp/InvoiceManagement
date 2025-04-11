<?php

namespace App\Console\Commands;

use App\Mail\InvoiceDueNotification;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceDueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-invoice-due-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia emails de alerta para faturas com vencimento hoje';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->format('Y-m-d');
        
        $this->info("A procurar faturas com vencimento para: {$today}");
        
        // Obter todas as faturas pendentes com vencimento para hoje
        $dueInvoices = Invoice::where('status', 'pending')
            ->whereDate('due_date', $today)
            ->with('supplier')
            ->get();
            
        $count = $dueInvoices->count();
        
        if ($count === 0) {
            $this->info('Não foram encontradas faturas com vencimento para hoje.');
            return;
        }
        
        $this->info("Encontradas {$count} faturas com vencimento para hoje.");
        
        // Obter todos os utilizadores administradores para receber as notificações
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->error('Não foram encontrados utilizadores para enviar notificações.');
            return;
        }
        
        $emailsSent = 0;
        
        // Para cada fatura, enviar email para cada administrador
        foreach ($dueInvoices as $invoice) {
            foreach ($users as $user) {
                try {
                    Mail::to($user->email)
                        ->send(new InvoiceDueNotification($user, $invoice));
                    
                    $emailsSent++;
                    
                    $this->info("Email enviado para {$user->email} sobre a fatura {$invoice->invoice_number}");
                    
                } catch (\Exception $e) {
                    $this->error("Erro ao enviar email para {$user->email}: {$e->getMessage()}");
                    Log::error("Erro ao enviar notificação de vencimento de fatura: {$e->getMessage()}", [
                        'invoice_id' => $invoice->id,
                        'user_id' => $user->id
                    ]);
                }
            }
        }
        
        $this->info("Concluído. Total de emails enviados: {$emailsSent}");
    }
}
