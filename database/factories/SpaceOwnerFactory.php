<?php

namespace Database\Factories;

use App\Models\SpaceOwner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpaceOwner>
 */
class SpaceOwnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->admin(),
            'nama_coworking' => fake()->company() . ' Coworking',
            'nama_pemilik' => fake()->name(),
            'telp' => '08' . fake()->numerify('##########'),
            'alamat' => fake()->address(),
            'deskripsi' => fake()->sentence(),
        ];
    }
}
