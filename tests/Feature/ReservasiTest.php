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

function makeMemberUser(array $overrides = []): User
{
    $user = User::factory()->create(array_merge([
        'username' => 'member_' . uniqid(),
        'password' => 'Member123!',
    ], $overrides));
    Member::factory()->create(['user_id' => $user->id]);

    return $user;
}

function makeSpace(int $harga = 20000, string $tipe = 'desk'): Space
{
    $ownerUser = User::factory()->admin()->create();
    $owner = SpaceOwner::factory()->create(['user_id' => $ownerUser->id]);

    return Space::factory()->create([
        'owner_id' => $owner->id,
        'harga_per_jam' => $harga,
        'tipe' => $tipe,
    ]);
}

it('member can create a reservation', function () {
    $user = makeMemberUser();
    $space = makeSpace(20000);
    $diskon = Diskon::factory()->create(['persentase_diskon' => 20]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/reservasi', [
        'id_space' => $space->id,
        'tanggal_reservasi' => Carbon::now()->addDays(3)->format('Y-m-d'),
        'jam_mulai' => '09:00',
        'durasi_jam' => 3,
        'id_diskon' => $diskon->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.status', 'belum_dikonfirm')
        ->assertJsonPath('data.total_harga_awal', 60000)
        ->assertJsonPath('data.potongan_diskon', 12000)
        ->assertJsonPath('data.total_bayar', 48000)
        ->assertJsonPath('data.jam_selesai', '12:00');

    $this->assertMatchesRegularExpression('/^BOOK-\d{8}-\d{4}$/', $response->json('data.kode_booking'));
});

it('rejects overlapping reservation on same space and date', function () {
    $user = makeMemberUser();
    $space = makeSpace();
    $tanggal = Carbon::now()->addDays(3)->format('Y-m-d');

    Reservasi::factory()->create([
        'space_id' => $space->id,
        'tanggal_reservasi' => $tanggal,
        'jam_mulai' => '09:00',
        'jam_selesai' => '12:00',
        'durasi_jam' => 3,
        'status' => 'disetujui',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/reservasi', [
        'id_space' => $space->id,
        'tanggal_reservasi' => $tanggal,
        'jam_mulai' => '10:00',
        'durasi_jam' => 2,
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('message', 'Maaf, space sudah terisi atau dibooking pada jam tersebut!');
});

it('rejects create reservation by non-member', function () {
    $adminUser = User::factory()->admin()->create();

    Sanctum::actingAs($adminUser);

    $response = $this->postJson('/api/reservasi', [
        'id_space' => 1,
        'tanggal_reservasi' => '2026-09-20',
        'jam_mulai' => '09:00',
        'durasi_jam' => 1,
    ]);

    $response->assertStatus(403);
});

it('member can cancel own reservation', function () {
    $user = makeMemberUser();
    $space = makeSpace();
    $member = Member::where('user_id', $user->id)->first();

    $reservasi = Reservasi::factory()->create([
        'member_id' => $member->id,
        'space_id' => $space->id,
        'status' => 'disetujui',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/reservasi/{$reservasi->id}/cancel");

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'dibatalkan');
});

it('member cannot cancel another member reservation', function () {
    $user = makeMemberUser();
    $space = makeSpace();

    $otherUser = User::factory()->create();
    $otherMember = Member::factory()->create(['user_id' => $otherUser->id]);

    $reservasi = Reservasi::factory()->create([
        'member_id' => $otherMember->id,
        'space_id' => $space->id,
        'status' => 'belum_dikonfirm',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/reservasi/{$reservasi->id}/cancel");

    $response->assertStatus(403);
});

it('cannot cancel reservation in final status', function () {
    $user = makeMemberUser();
    $space = makeSpace();
    $member = Member::where('user_id', $user->id)->first();

    $reservasi = Reservasi::factory()->create([
        'member_id' => $member->id,
        'space_id' => $space->id,
        'status' => 'selesai',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/reservasi/{$reservasi->id}/cancel");

    $response->assertStatus(400);
});

it('member cannot access another member reservation detail', function () {
    $user = makeMemberUser();

    $otherUser = User::factory()->create();
    $otherMember = Member::factory()->create(['user_id' => $otherUser->id]);
    $space = makeSpace();

    $reservasi = Reservasi::factory()->create([
        'member_id' => $otherMember->id,
        'space_id' => $space->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/reservasi/{$reservasi->id}");

    $response->assertStatus(403);
});

it('returns history with totals', function () {
    $user = makeMemberUser();
    $space = makeSpace(20000);
    $member = Member::where('user_id', $user->id)->first();

    $bulan = now()->month;
    $tahun = now()->year;

    Reservasi::factory()->create([
        'member_id' => $member->id,
        'space_id' => $space->id,
        'tanggal_reservasi' => Carbon::now()->format('Y-m-d'),
        'durasi_jam' => 2,
        'total_bayar' => 40000,
        'status' => 'selesai',
    ]);
    Reservasi::factory()->create([
        'member_id' => $member->id,
        'space_id' => $space->id,
        'tanggal_reservasi' => Carbon::now()->addDays(2)->format('Y-m-d'),
        'durasi_jam' => 1,
        'total_bayar' => 20000,
        'status' => 'disetujui',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/reservasi/my/history?month={$bulan}&year={$tahun}");

    $response->assertStatus(200)
        ->assertJsonPath('data.total_reservasi', 2)
        ->assertJsonPath('data.total_pengeluaran', 60000);
});
