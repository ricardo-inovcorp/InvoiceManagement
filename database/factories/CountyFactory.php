<?php

namespace Database\Factories;

use App\Models\County;
use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\County>
 */
class CountyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $district = District::inRandomOrder()->first() ?? District::factory()->create();
        $name = $this->faker->unique()->city();
        $code = $district->code . strtoupper(mb_substr($name, 0, 3));
        
        return [
            'name' => $name,
            'code' => $code,
            'district_id' => $district->id,
        ];
    }
}
