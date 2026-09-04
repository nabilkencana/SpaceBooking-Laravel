<?php

namespace Database\Factories;

use App\Models\Space;
use App\Models\SpaceOwner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Space>
 */
class SpaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => SpaceOwner::factory(),
            'nama_space' => fake()->unique()->words(3, true),
            'harga_per_jam' => fake()->numberBetween(15000, 200000),
            'tipe' => fake()->randomElement(['desk', 'meeting_room', 'private_office']),
            'kapasitas' => fake()->numberBetween(1, 20),
            'deskripsi' => fake()->sentence(),
            'foto' => null,
        ];
    }
}
