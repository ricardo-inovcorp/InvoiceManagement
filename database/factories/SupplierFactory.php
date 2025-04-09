<?php

namespace Database\Factories;

use App\Models\County;
use App\Models\District;
use App\Models\OrganizationType;
use App\Models\Sector;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Buscar dados das tabelas relacionadas
        $sector = Sector::inRandomOrder()->first() ?? Sector::factory()->create();
        $organizationType = OrganizationType::inRandomOrder()->first() ?? OrganizationType::factory()->create();
        $district = District::inRandomOrder()->first() ?? District::factory()->create();
        $county = County::where('district_id', $district->id)->inRandomOrder()->first();
        
        // Se não houver concelhos para o distrito, usar null (ou criar um)
        if (!$county) {
            $county = null;
        }
        
        // Gerar um nome de empresa baseado no setor
        $companyName = $this->faker->company();
        
        // Código postal português (4 números - 3 números)
        $cp4 = $this->faker->numberBetween(1000, 9999);
        $cp3 = $this->faker->numberBetween(1, 999);
        $cp3 = str_pad($cp3, 3, '0', STR_PAD_LEFT);
        $codigoPostal = "{$cp4}-{$cp3}";
        
        // Gerar um NIF válido para empresa (começa com 5)
        $nif = '5' . $this->faker->numerify('########');
        
        return [
            'company_name' => $companyName,
            'document' => $nif,
            'email' => $this->faker->companyEmail(),
            'phone' => '+351 ' . $this->faker->numberBetween(911111111, 969999999),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'zip_code' => $codigoPostal,
            'notes' => $this->faker->optional(0.7)->sentence(10),
            'active' => $this->faker->boolean(90), // 90% são ativos
            'sector_id' => $sector->id,
            'organization_type_id' => $organizationType->id,
            'district_id' => $district->id,
            'county_id' => $county ? $county->id : null,
        ];
    }
    
    /**
     * Indica que o fornecedor está ativo.
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => true,
            ];
        });
    }
    
    /**
     * Indica que o fornecedor está inativo.
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => false,
            ];
        });
    }
}
