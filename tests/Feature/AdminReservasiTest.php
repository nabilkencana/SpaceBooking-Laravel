<?php

use App\Models\Member;
use App\Models\Reservasi;
use App\Models\Space;
use App\Models\SpaceOwner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function makeAdmin(array $ownerOverrides = []): array
{
    $user = User::factory()->admin()->create(['username' => 'admin_' . uniqid()]);
    $owner = SpaceOwner::factory()->create(array_merge(['user_id' => $user->id], $ownerOverrides));

    return [$user, $owner];
}

function makeAdminOwnedReservasi(string $status = 'belum_dikonfirm'): array
{
    [$admin, $owner] = makeAdmin();
    $space = Space::factory()->create(['owner_id' => $owner->id]);

    $memberUser = User::factory()->create();
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $reservasi = Reservasi::factory()->create([
        'member_id' => $member->id,
        'space_id' => $space->id,
        'status' => $status,
        'tanggal_reservasi' => Carbon::now()->addDays(2)->format('Y-m-d'),
    ]);

    return [$admin, $owner, $space, $reservasi];
}

it('admin can approve a reservation', function () {
    [$admin, $owner, $space, $reservasi] = makeAdminOwnedReservasi('belum_dikonfirm');

    Sanctum::actingAs($admin);

    $response = $this->patchJson("/api/admin/reservasi/{$reservasi->id}/status", [
        'status' => 'disetujui',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'disetujui');
});

it('admin can check-in an approved reservation', function () {
    [$admin, $owner, $space, $reservasi] = makeAdminOwnedReservasi('disetujui');

    Sanctum::actingAs($admin);

    $response = $this->postJson("/api/admin/reservasi/{$reservasi->id}/check-in");

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'aktif');
    expect($response->json('data.check_in_time'))->not->toBeNull();
});

it('admin can check-out an active reservation', function () {
    [$admin, $owner, $space, $reservasi] = makeAdminOwnedReservasi('aktif');

    Sanctum::actingAs($admin);

    $response = $this->postJson("/api/admin/reservasi/{$reservasi->id}/check-out");

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'selesai');
    expect($response->json('data.check_out_time'))->not->toBeNull();
});

it('rejects check-in on non-approved reservation', function () {
    [$admin, $owner, $space, $reservasi] = makeAdminOwnedReservasi('belum_dikonfirm');

    Sanctum::actingAs($admin);

    $response = $this->postJson("/api/admin/reservasi/{$reservasi->id}/check-in");

    $response->assertStatus(400);
});

it('rejects check-out on non-active reservation', function () {
    [$admin, $owner, $space, $reservasi] = makeAdminOwnedReservasi('disetujui');

    Sanctum::actingAs($admin);

    $response = $this->postJson("/api/admin/reservasi/{$reservasi->id}/check-out");

    $response->assertStatus(400);
});

it('rejects changing final status', function () {
    [$admin, $owner, $space, $reservasi] = makeAdminOwnedReservasi('selesai');

    Sanctum::actingAs($admin);

    $response = $this->patchJson("/api/admin/reservasi/{$reservasi->id}/status", [
        'status' => 'dibatalkan',
    ]);

    $response->assertStatus(400);
});

it('rejects admin modifying reservation of another owner space', function () {
    [$admin, $owner] = makeAdmin();

    // Reservasi milik owner lain
    $otherAdmin = User::factory()->admin()->create();
    $otherOwner = SpaceOwner::factory()->create(['user_id' => $otherAdmin->id]);
    $otherSpace = Space::factory()->create(['owner_id' => $otherOwner->id]);

    $memberUser = User::factory()->create();
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $reservasi = Reservasi::factory()->create([
        'member_id' => $member->id,
        'space_id' => $otherSpace->id,
        'status' => 'belum_dikonfirm',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->patchJson("/api/admin/reservasi/{$reservasi->id}/status", [
        'status' => 'disetujui',
    ]);

    $response->assertStatus(404);
});

it('admin can list only their own space reservations', function () {
    [$admin, $owner, $space, $reservasi] = makeAdminOwnedReservasi('belum_dikonfirm');

    // Reservasi space milik owner lain
    $otherAdmin = User::factory()->admin()->create();
    $otherOwner = SpaceOwner::factory()->create(['user_id' => $otherAdmin->id]);
    $otherSpace = Space::factory()->create(['owner_id' => $otherOwner->id]);
    $memberUser = User::factory()->create();
    $member = Member::factory()->create(['user_id' => $memberUser->id]);
    Reservasi::factory()->create([
        'member_id' => $member->id,
        'space_id' => $otherSpace->id,
        'status' => 'belum_dikonfirm',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/reservasi');

    $response->assertStatus(200);
    $this->assertCount(1, $response->json('data'));
    $this->assertEquals($reservasi->id, $response->json('data.0.id'));
});

it('blocks member from admin endpoints', function () {
    $user = User::factory()->create();
    Member::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/admin/reservasi');

    $response->assertStatus(403);
});
