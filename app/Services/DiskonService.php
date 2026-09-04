<?php

namespace App\Services;

use App\Models\Diskon;
use Carbon\Carbon;
use RuntimeException;

class DiskonService
{
    /**
     * Cek apakah diskon sedang aktif (now di antara tanggal_awal dan tanggal_akhir).
     */
    public function isAktif(Diskon $diskon): bool
    {
        $now = Carbon::now();

        return $now->between($diskon->tanggal_awal, $diskon->tanggal_akhir);
    }

    /**
     * Validasi kode promo berdasarkan nama_diskon.
     * Return objek Diskon jika valid dan aktif.
     *
     * @throws RuntimeException Jika kode tidak ditemukan / tidak aktif
     */
    public function validasiKodePromo(string $kodePromo): Diskon
    {
        $diskon = Diskon::where('nama_diskon', $kodePromo)->first();

        if (! $diskon || ! $this->isAktif($diskon)) {
            throw new RuntimeException('Kode promo tidak ditemukan atau sudah kedaluwarsa!', 400);
        }

        return $diskon;
    }

    /**
     * Cari diskon berdasarkan ID, pastikan aktif.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws RuntimeException Jika diskon tidak aktif
     */
    public function findAktifById(int $id): Diskon
    {
        $diskon = Diskon::findOrFail($id);

        if (! $this->isAktif($diskon)) {
            throw new RuntimeException('Kode promo tidak ditemukan atau sudah kedaluwarsa!', 400);
        }

        return $diskon;
    }
}
