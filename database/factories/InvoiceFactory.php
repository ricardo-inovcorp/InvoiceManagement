<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Gerar uma data de emissão nos últimos 180 dias
        $issueDate = $this->faker->dateTimeBetween('-180 days', 'now');
        
        // Data de vencimento (tipicamente entre 30 e 90 dias após a emissão)
        $paymentTerms = $this->faker->randomElement([30, 45, 60, 90]);
        $dueDate = (new Carbon($issueDate))->addDays($paymentTerms);
        
        // Determinar o status baseado na data de vencimento
        $today = new Carbon();
        $status = 'pending';
        
        if ($dueDate < $today) {
            // Fatura vencida - 70% chance de ser paga mesmo vencida, 30% de estar em atraso
            $status = $this->faker->randomElement(['paid', 'paid', 'paid', 'paid', 'paid', 'paid', 'paid', 'overdue', 'overdue', 'overdue']);
        } else {
            // Fatura ainda dentro do prazo - 50% chance de já ter sido paga
            $status = $this->faker->randomElement(['paid', 'paid', 'paid', 'paid', 'paid', 'pending', 'pending', 'pending', 'pending', 'pending']);
        }
        
        // Se foi paga, definir uma data de pagamento
        $paymentDate = null;
        if ($status === 'paid') {
            // Pagamento entre a data de emissão e hoje (ou até 15 dias após o vencimento)
            $latestPayDate = min($today, (new Carbon($dueDate))->addDays(15));
            $paymentDate = $this->faker->dateTimeBetween($issueDate, $latestPayDate);
        }
        
        // Gerar um valor base para a fatura
        $baseAmount = $this->faker->numberBetween(100, 10000);
        $taxRate = 0.23; // IVA padrão em Portugal (23%)
        $taxAmount = round($baseAmount * $taxRate, 2);
        $totalAmount = $baseAmount + $taxAmount;
        
        // Métodos de pagamento comuns em Portugal
        $paymentMethods = [
            'Transferência Bancária',
            'Multibanco',
            'MBWay',
            'Débito Direto',
            'Cartão de Crédito',
            'Cheque',
            'Numerário'
        ];
        
        // Gerar um número de fatura seguindo o formato português (ANO/NÚMERO SEQUENCIAL)
        $year = date('Y', $issueDate->getTimestamp());
        $invoiceNumber = $year . '/' . $this->faker->unique()->numberBetween(1000, 9999);
        
        return [
            'supplier_id' => Supplier::inRandomOrder()->first() ?? Supplier::factory()->create(),
            'invoice_number' => $invoiceNumber,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'base_amount' => $baseAmount,
            'total_amount' => $totalAmount,
            'tax_amount' => $taxAmount,
            'status' => $status,
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'payment_date' => $paymentDate,
            'notes' => $this->faker->optional(0.5)->sentence(8),
            'file_path' => $this->faker->optional(0.3)->imageUrl(640, 480, 'documents'),
        ];
    }
    
    /**
     * Indica que a fatura está paga.
     */
    public function paid()
    {
        return $this->state(function (array $attributes) {
            $paymentDate = $this->faker->dateTimeBetween($attributes['issue_date'], 'now');
            
            return [
                'status' => 'paid',
                'payment_date' => $paymentDate,
            ];
        });
    }
    
    /**
     * Indica que a fatura está pendente.
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'payment_date' => null,
            ];
        });
    }
    
    /**
     * Indica que a fatura está em atraso.
     */
    public function overdue()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'overdue',
                'payment_date' => null,
                'due_date' => Carbon::now()->subDays($this->faker->numberBetween(1, 90)),
            ];
        });
    }
}
