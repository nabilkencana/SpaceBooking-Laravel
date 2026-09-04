<?php

namespace Database\Factories;

use App\Models\Diskon;
use App\Models\Member;
use App\Models\Reservasi;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservasi>
 */
class ReservasiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jamMulai = fake()->numberBetween(7, 18);
        $durasi = fake()->numberBetween(1, 8);
        $space = Space::factory()->create();
        $hargaPerJam = $space->harga_per_jam;
        $totalAwal = $hargaPerJam * $durasi;
        $potongan = 0;
        $diskonId = null;

        return [
            'kode_booking' => fake()->unique()->numerify('BOOK-20260904-####'),
            'member_id' => Member::factory(),
            'space_id' => $space->id,
            'diskon_id' => $diskonId,
            'tanggal_reservasi' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'jam_mulai' => sprintf('%02d:00', $jamMulai),
            'jam_selesai' => sprintf('%02d:00', $jamMulai + $durasi),
            'durasi_jam' => $durasi,
            'harga_per_jam' => $hargaPerJam,
            'total_harga_awal' => $totalAwal,
            'potongan_diskon' => $potongan,
            'total_bayar' => $totalAwal - $potongan,
            'status' => 'belum_dikonfirm',
            'check_in_at' => null,
            'check_out_at' => null,
        ];
    }

    /**
     * Set the reservation status.
     */
    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
