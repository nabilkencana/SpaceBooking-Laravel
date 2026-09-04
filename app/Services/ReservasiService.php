<?php

namespace App\Services;

use App\Models\Diskon;
use App\Models\Reservasi;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

class ReservasiService
{
    /**
     * Cek apakah ada bentrok jadwal pada space yang sama, tanggal yang sama,
     * dengan status selain 'dibatalkan'.
     * Rumus overlap: existing.jam_mulai < new.jam_selesai AND new.jam_mulai < existing.jam_selesai
     *
     * @throws RuntimeException Jika ditemukan bentrok
     */
    public function cekBentrok(
        int $spaceId,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        ?int $excludeId = null
    ): void {
        $overlap = Reservasi::where('space_id', $spaceId)
            ->where('tanggal_reservasi', $tanggal)
            ->where('status', '!=', 'dibatalkan')
            ->when($excludeId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai)
            ->exists();

        if ($overlap) {
            throw new RuntimeException('Maaf, space sudah terisi atau dibooking pada jam tersebut!', 400);
        }
    }

    /**
     * Hitung harga: total_harga_awal = harga_per_jam * durasi_jam
     * potongan = round(total * persentase / 100) jika diskon aktif
     * total_bayar = total_harga_awal - potongan_diskon
     *
     * @return array{harga_per_jam:int,total_harga_awal:int,potongan_diskon:int,total_bayar:int,diskon_id:?int}
     */
    public function hitungHarga(Space $space, int $durasiJam, ?Diskon $diskon = null): array
    {
        $totalAwal = $space->harga_per_jam * $durasiJam;
        $isAktif = $diskon && app(DiskonService::class)->isAktif($diskon);
        $potongan = $isAktif ? (int) round($totalAwal * $diskon->persentase_diskon / 100) : 0;

        return [
            'harga_per_jam' => $space->harga_per_jam,
            'total_harga_awal' => $totalAwal,
            'potongan_diskon' => $potongan,
            'total_bayar' => $totalAwal - $potongan,
            'diskon_id' => $isAktif ? $diskon->id : null,
        ];
    }

    /**
     * Generate kode booking unik: BOOK-YYYYMMDD-XXXX
     * XXXX = 4 digit acak. Retry jika collision di DB.
     */
    public function generateKodeBooking(string $tanggalReservasi): string
    {
        $tanggalFormat = Carbon::parse($tanggalReservasi)->format('Ymd');

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $kode = sprintf('BOOK-%s-%04d', $tanggalFormat, random_int(0, 9999));

            if (! Reservasi::where('kode_booking', $kode)->exists()) {
                return $kode;
            }
        }

        return sprintf('BOOK-%s-%s', $tanggalFormat, Str::upper(Str::random(4)));
    }

    /**
     * Hitung jam_selesai dari jam_mulai + durasi_jam.
     * Return string format H:i.
     */
    public function hitungJamSelesai(string $jamMulai, int $durasiJam): string
    {
        return Carbon::parse($jamMulai)->addHours($durasiJam)->format('H:i');
    }

    /**
     * Helper resolve diskon dari request: prioritaskan id_diskon, fallback kode_promo.
     * Jika keduanya tidak ada, return null.
     *
     * @throws RuntimeException Jika kode promo tidak valid
     */
    public function resolveDiskon(?int $idDiskon, ?string $kodePromo): ?Diskon
    {
        $service = app(DiskonService::class);

        return match (true) {
            ! empty($idDiskon) => $service->findAktifById($idDiskon),
            ! empty($kodePromo) => $service->validasiKodePromo($kodePromo),
            default => null,
        };
    }

    private const TRANSISI = [
        'belum_dikonfirm' => ['member' => ['dibatalkan'], 'admin' => ['disetujui', 'dibatalkan']],
        'disetujui'       => ['member' => ['dibatalkan'], 'admin' => ['dibatalkan'], 'checkin' => ['aktif']],
        'aktif'           => ['member' => [], 'admin' => [], 'checkout' => ['selesai']],
        'selesai'         => ['member' => [], 'admin' => []],
        'dibatalkan'      => ['member' => [], 'admin' => []],
    ];

    /**
     * Validasi transisi status reservasi berdasarkan peran aktor.
     *
     * @throws RuntimeException Jika transisi tidak diizinkan
     */
    public function validasiTransisi(string $dari, string $ke, string $aktor): void
    {
        $diizinkan = self::TRANSISI[$dari][$aktor] ?? [];

        if (! in_array($ke, $diizinkan, true)) {
            throw new RuntimeException("Transisi dari {$dari} ke {$ke} oleh {$aktor} tidak diizinkan.", 400);
        }
    }
}
