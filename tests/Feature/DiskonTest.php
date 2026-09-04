<?php

use App\Models\Diskon;
use App\Models\SpaceOwner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lists only active diskons', function () {
    Diskon::factory()->create(['tanggal_awal' => Carbon::now()->subDays(5), 'tanggal_akhir' => Carbon::now()->addDays(5)]);
    Diskon::factory()->create(['tanggal_awal' => Carbon::now()->subDays(30), 'tanggal_akhir' => Carbon::now()->subDays(10)]);

    $response = $this->getJson('/api/diskon/active');

    $response->assertStatus(200);
    $this->assertCount(1, $response->json('data'));
});

it('validates an active promo code', function () {
    $diskon = Diskon::factory()->create([
        'nama_diskon' => 'DISKON20',
        'tanggal_awal' => Carbon::now()->subDays(5),
        'tanggal_akhir' => Carbon::now()->addDays(5),
    ]);

    $response = $this->postJson('/api/diskon/check', ['nama_diskon' => 'DISKON20']);

    $response->assertStatus(200)
        ->assertJsonPath('data.nama_diskon', 'DISKON20')
        ->assertJsonPath('data.is_active', true);
});

it('rejects expired promo code', function () {
    Diskon::factory()->create([
        'nama_diskon' => 'EXPIRED',
        'tanggal_awal' => Carbon::now()->subDays(30),
        'tanggal_akhir' => Carbon::now()->subDays(10),
    ]);

    $response = $this->postJson('/api/diskon/check', ['nama_diskon' => 'EXPIRED']);

    $response->assertStatus(400);
});

it('rejects non-existent promo code', function () {
    $response = $this->postJson('/api/diskon/check', ['nama_diskon' => 'NOTFOUND']);

    $response->assertStatus(400);
});

it('shows diskon detail', function () {
    $diskon = Diskon::factory()->create();

    $response = $this->getJson("/api/diskon/{$diskon->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $diskon->id);
});

it('admin can create a diskon', function () {
    $admin = User::factory()->admin()->create();
    SpaceOwner::factory()->create(['user_id' => $admin->id]);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/diskon', [
        'nama_diskon' => 'PROMOAGUSTUS',
        'persentase_diskon' => 20,
        'tanggal_awal' => '2026-08-01T00:00:00Z',
        'tanggal_akhir' => '2026-08-31T23:59:59Z',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.nama_diskon', 'PROMOAGUSTUS');

    $this->assertDatabaseHas('diskons', ['nama_diskon' => 'PROMOAGUSTUS']);
});
