<?php

use App\Models\Diskon;
use App\Models\Member;
use App\Models\Reservasi;
use App\Models\Space;
use App\Models\SpaceOwner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function makeReportOwner(): array
{
    $admin = User::factory()->admin()->create();
    $owner = SpaceOwner::factory()->create(['user_id' => $admin->id]);

    return [$admin, $owner];
}

function makeReportReservasi(int $ownerId, array $attrs): Reservasi
{
    $space = Space::factory()->create([
        'owner_id' => $ownerId,
        'harga_per_jam' => $attrs['harga_per_jam'],
        'tipe' => $attrs['tipe'],
    ]);
    $memberUser = User::factory()->create();
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $durasi = $attrs['durasi_jam'];
    $totalAwal = $attrs['harga_per_jam'] * $durasi;
    $potongan = isset($attrs['potongan']) ? $attrs['potongan'] : 0;

    return Reservasi::factory()->create([
        'member_id' => $member->id,
        'space_id' => $space->id,
        'tanggal_reservasi' => $attrs['tanggal']->format('Y-m-d'),
        'durasi_jam' => $durasi,
        'total_harga_awal' => $totalAwal,
        'potongan_diskon' => $potongan,
        'total_bayar' => $totalAwal - $potongan,
        'status' => $attrs['status'],
    ]);
}

it('returns monthly report aggregation', function () {
    [$admin, $owner] = makeReportOwner();

    $bulan = now()->month;
    $tahun = now()->year;

    // 2 reservasi selesai di bulan ini (masuk realisasi)
    makeReportReservasi($owner->id, [
        'harga_per_jam' => 20000, 'tipe' => 'desk', 'durasi_jam' => 3,
        'potongan' => 0, 'status' => 'selesai', 'tanggal' => now(),
    ]);
    makeReportReservasi($owner->id, [
        'harga_per_jam' => 100000, 'tipe' => 'meeting_room', 'durasi_jam' => 2,
        'potongan' => 20000, 'status' => 'selesai', 'tanggal' => now(),
    ]);
    // 1 reservasi dibatalkan (tidak masuk realisasi)
    makeReportReservasi($owner->id, [
        'harga_per_jam' => 20000, 'tipe' => 'desk', 'durasi_jam' => 5,
        'potongan' => 0, 'status' => 'dibatalkan', 'tanggal' => now(),
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson("/api/admin/reports/monthly?month={$bulan}&year={$tahun}");

    $response->assertStatus(200)
        ->assertJsonPath('data.total_transaksi', 3)
        ->assertJsonPath('data.total_jam_terpakai', 5)
        ->assertJsonPath('data.estimasi_pendapatan_kotor', 360000)
        ->assertJsonPath('data.total_potongan_diskon', 20000)
        ->assertJsonPath('data.realisasi_pendapatan_bersih', 240000);
});

it('returns income alias', function () {
    [$admin, $owner] = makeReportOwner();

    makeReportReservasi($owner->id, [
        'harga_per_jam' => 20000, 'tipe' => 'desk', 'durasi_jam' => 2,
        'potongan' => 0, 'status' => 'selesai', 'tanggal' => now(),
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/reports/income?month=' . now()->month . '&year=' . now()->year);

    $response->assertStatus(200)
        ->assertJsonPath('data.realisasi_pendapatan_bersih', 40000);
});
