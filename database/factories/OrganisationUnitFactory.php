<?php

namespace Database\Factories;

use App\Models\OrganisationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganisationUnit>
 */
class OrganisationUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'parish',
            'name' => fake()->unique()->city().' Parish',
            'is_active' => true,
        ];
    }
}
