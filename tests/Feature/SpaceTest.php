<?php

use App\Models\Reservasi;
use App\Models\Space;
use App\Models\SpaceOwner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSpaceForCatalog(string $tipe = 'desk', ?string $nama = null, int $harga = 20000): Space
{
    $admin = User::factory()->admin()->create();
    $owner = SpaceOwner::factory()->create(['user_id' => $admin->id, 'nama_coworking' => 'Test Hub']);

    return Space::factory()->create([
        'owner_id' => $owner->id,
        'tipe' => $tipe,
        'nama_space' => $nama ?? fake()->unique()->words(2, true),
        'harga_per_jam' => $harga,
    ]);
}

it('lists all spaces', function () {
    makeSpaceForCatalog();
    makeSpaceForCatalog('meeting_room');

    $response = $this->getJson('/api/spaces');

    $response->assertStatus(200)
        ->assertJsonPath('status', true);
    $this->assertCount(2, $response->json('data'));
});

it('filters spaces by type', function () {
    makeSpaceForCatalog('desk');
    makeSpaceForCatalog('meeting_room');

    $response = $this->getJson('/api/spaces?tipe=desk');

    $response->assertStatus(200);
    $this->assertCount(1, $response->json('data'));
    $this->assertEquals('desk', $response->json('data.0.tipe'));
});

it('searches spaces by name', function () {
    $space = makeSpaceForCatalog('desk', 'Alpha Workspace');

    $response = $this->getJson('/api/spaces?search=Alpha');

    $response->assertStatus(200);
    $this->assertCount(1, $response->json('data'));
    $this->assertEquals($space->id, $response->json('data.0.id'));
});

it('returns space types', function () {
    $response = $this->getJson('/api/spaces/types');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.tipe', 'desk')
        ->assertJsonPath('data.1.tipe', 'meeting_room')
        ->assertJsonPath('data.2.tipe', 'private_office');
    $this->assertCount(3, $response->json('data'));
});

it('shows space detail with owner', function () {
    $space = makeSpaceForCatalog('desk', 'Detail Space');

    $response = $this->getJson("/api/spaces/{$space->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $space->id)
        ->assertJsonPath('data.nama_space', 'Detail Space')
        ->assertJsonPath('data.owner.nama_coworking', 'Test Hub');
});

it('returns 404 for non-existent space', function () {
    $response = $this->getJson('/api/spaces/99999');

    $response->assertStatus(404)
        ->assertJsonPath('status', false);
});

it('reports space availability when free', function () {
    $space = makeSpaceForCatalog('desk', 'Avail Space', 25000);

    $response = $this->getJson('/api/spaces/availability?id_space=' . $space->id . '&tanggal=' . Carbon::now()->addDays(5)->format('Y-m-d') . '&jam_mulai=09:00&durasi_jam=2');

    $response->assertStatus(200)
        ->assertJsonPath('data.available', true)
        ->assertJsonPath('data.estimasi_total', 50000)
        ->assertJsonPath('data.jam_selesai', '11:00');
});

it('reports space not available when booked', function () {
    $space = makeSpaceForCatalog('desk', 'Booked Space');
    $tanggal = Carbon::now()->addDays(5)->format('Y-m-d');

    $memberUser = User::factory()->create();
    $member = \App\Models\Member::factory()->create(['user_id' => $memberUser->id]);

    Reservasi::factory()->create([
        'space_id' => $space->id,
        'member_id' => $member->id,
        'tanggal_reservasi' => $tanggal,
        'jam_mulai' => '09:00',
        'jam_selesai' => '11:00',
        'durasi_jam' => 2,
        'status' => 'disetujui',
    ]);

    $response = $this->getJson('/api/spaces/availability?id_space=' . $space->id . '&tanggal=' . $tanggal . '&jam_mulai=10:00&durasi_jam=1');

    $response->assertStatus(400);
});
