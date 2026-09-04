<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterAdminSpaceRequest;
use App\Http\Requests\Auth\RegisterMemberRequest;
use App\Http\Resources\UserResource;
use App\Models\Member;
use App\Models\SpaceOwner;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function registerMember(RegisterMemberRequest $request): JsonResponse
    {
        try {
            $user = DB::transaction(function () use ($request) {
                $user = User::create($request->only('username', 'password') + ['role' => 'member']);
                $user->member()->create($request->only('nama_member', 'instansi', 'alamat', 'telp', 'foto'));

                return $user;
            });

            return $this->created([
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'member' => $user->member,
                'access_token' => $user->createToken('api-token')->plainTextToken,
            ], 'Registrasi member berhasil!');
        } catch (\Throwable $e) {
            return $this->error('Registrasi gagal: ' . $e->getMessage(), 'Bad Request', 400);
        }
    }

    public function registerAdminSpace(RegisterAdminSpaceRequest $request): JsonResponse
    {
        try {
            $user = DB::transaction(function () use ($request) {
                $user = User::create($request->only('username', 'password') + ['role' => 'admin_space']);
                $user->spaceOwner()->create($request->only('nama_coworking', 'nama_pemilik', 'telp', 'alamat', 'deskripsi'));

                return $user;
            });

            return $this->created([
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'space_owner' => $user->spaceOwner,
                'access_token' => $user->createToken('api-token')->plainTextToken,
            ], 'Registrasi Admin Space berhasil!');
        } catch (\Throwable $e) {
            return $this->error('Registrasi gagal: ' . $e->getMessage(), 'Bad Request', 400);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('Username atau Password salah!', 'Unauthorized', 401);
        }

        $user->load($user->role === 'member' ? 'member' : 'spaceOwner');

        return $this->success([
            'id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'member' => $user->member,
            'space_owner' => $user->spaceOwner,
            'access_token' => $user->createToken('api-token')->plainTextToken,
        ], 'Login berhasil!');
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load($request->user()->role === 'member' ? 'member' : 'spaceOwner');

        return $this->success(new UserResource($user), 'Berhasil memproses permintaan');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout berhasil!');
    }
}
