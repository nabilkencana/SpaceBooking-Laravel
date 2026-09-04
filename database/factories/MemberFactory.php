<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nama_member' => fake()->name(),
            'instansi' => fake()->company(),
            'alamat' => fake()->address(),
            'telp' => '08' . fake()->numerify('##########'),
            'foto' => null,
        ];
    }
}
