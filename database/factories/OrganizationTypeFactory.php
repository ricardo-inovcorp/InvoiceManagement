<?php

namespace Database\Factories;

use App\Models\OrganizationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrganizationType>
 */
class OrganizationTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        $abbreviation = strtoupper(mb_substr($name, 0, 1)) . '.' . strtoupper(mb_substr($name, strpos($name, ' ') + 1, 1)) . '.';
        
        return [
            'name' => $name,
            'abbreviation' => $abbreviation,
            'description' => $this->faker->sentence(),
            'active' => true,
        ];
    }
}
