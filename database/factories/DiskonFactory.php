<?php

namespace Database\Factories;

use App\Models\Diskon;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Diskon>
 */
class DiskonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_diskon' => fake()->unique()->regexify('[A-Z]{5}[0-9]{2}'),
            'persentase_diskon' => fake()->numberBetween(1, 100),
            'tanggal_awal' => Carbon::now()->subDays(10),
            'tanggal_akhir' => Carbon::now()->addDays(10),
        ];
    }
}
