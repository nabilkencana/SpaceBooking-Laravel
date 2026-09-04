<?php

use App\Models\Member;
use App\Models\SpaceOwner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('can register a member', function () {
    $response = $this->postJson('/api/auth/register/member', [
        'username' => 'johndoe',
        'password' => 'Secret123!',
        'nama_member' => 'John Doe',
        'instansi' => 'Universitas Indonesia',
        'alamat' => 'Jl. Sudirman No. 123',
        'telp' => '081234567890',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.username', 'johndoe')
        ->assertJsonPath('data.role', 'member')
        ->assertJsonStructure(['data' => ['access_token']]);

    $this->assertDatabaseHas('members', ['nama_member' => 'John Doe']);
});

it('can register an admin space', function () {
    $response = $this->postJson('/api/auth/register/admin-space', [
        'username' => 'admin_space1',
        'password' => 'Admin123!',
        'nama_coworking' => 'Moklet Hub Coworking',
        'nama_pemilik' => 'Ahmad Bidin',
        'telp' => '081298765432',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.username', 'admin_space1')
        ->assertJsonPath('data.role', 'admin_space')
        ->assertJsonStructure(['data' => ['access_token']]);

    $this->assertDatabaseHas('space_owners', ['nama_coworking' => 'Moklet Hub Coworking']);
});

it('can login a member and return access token', function () {
    $user = User::factory()->create(['username' => 'johndoe', 'password' => 'Secret123!']);
    Member::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson('/api/auth/login', [
        'username' => 'johndoe',
        'password' => 'Secret123!',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.username', 'johndoe')
        ->assertJsonPath('data.role', 'member')
        ->assertJsonStructure(['data' => ['access_token']]);
});

it('rejects login with wrong credentials', function () {
    $user = User::factory()->create(['username' => 'johndoe', 'password' => 'Secret123!']);

    $response = $this->postJson('/api/auth/login', [
        'username' => 'johndoe',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('status', false)
        ->assertJsonPath('error', 'Unauthorized');
});

it('rejects profile access without token', function () {
    $response = $this->getJson('/api/auth/profile');

    $response->assertStatus(401)
        ->assertJsonPath('status', false);
});

it('can access profile with valid token', function () {
    $user = User::factory()->create(['username' => 'johndoe', 'password' => 'Secret123!']);
    $member = Member::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/auth/profile');

    $response->assertStatus(200)
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.username', 'johndoe')
        ->assertJsonPath('data.role', 'member')
        ->assertJsonPath('data.member.nama_member', $member->nama_member);
});

it('rejects duplicate username on register', function () {
    User::factory()->create(['username' => 'johndoe']);

    $response = $this->postJson('/api/auth/register/member', [
        'username' => 'johndoe',
        'password' => 'Secret123!',
        'nama_member' => 'John',
        'instansi' => 'Instansi',
        'alamat' => 'Alamat',
        'telp' => '081234567890',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('status', false);
});

it('returns admin space owner data on admin login', function () {
    $user = User::factory()->admin()->create(['username' => 'adminsatu', 'password' => 'Admin123!']);
    SpaceOwner::factory()->create(['user_id' => $user->id, 'nama_coworking' => 'Test Hub']);

    $response = $this->postJson('/api/auth/login', [
        'username' => 'adminsatu',
        'password' => 'Admin123!',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.role', 'admin_space')
        ->assertJsonPath('data.space_owner.nama_coworking', 'Test Hub');
});
