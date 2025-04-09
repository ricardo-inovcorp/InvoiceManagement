<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Produtos e serviços comuns em faturas
        $quantidade = $this->faker->numberBetween(1, 10);
        $precoUnitario = $this->faker->randomFloat(2, 10, 1000);
        $total = $quantidade * $precoUnitario;
        $taxRate = $this->faker->randomElement([6, 13, 23]); // Taxas de IVA em Portugal
        $taxAmount = $total * ($taxRate / 100);
        
        return [
            'invoice_id' => Invoice::factory(),
            'description' => $this->faker->sentence(3),
            'quantity' => $quantidade,
            'unit_price' => $precoUnitario,
            'total_price' => $total,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
        ];
    }
}
