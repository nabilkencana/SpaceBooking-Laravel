<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateMemberAdminRequest;
use App\Http\Requests\Admin\UpdateMemberAdminRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $members = Member::when($request->search, fn ($q, $s) =>
            $q->where(fn ($sub) => $sub->where('nama_member', 'like', "%{$s}%")
                ->orWhere('instansi', 'like', "%{$s}%")
                ->orWhere('telp', 'like', "%{$s}%"))
        )->orderBy('nama_member')->get();

        return $this->success(MemberResource::collection($members));
    }

    public function store(CreateMemberAdminRequest $request): JsonResponse
    {
        try {
            $member = DB::transaction(function () use ($request) {
                $user = User::create($request->only('username', 'password') + ['role' => 'member']);
                return $user->member()->create($request->except('username', 'password'));
            });

            return $this->created(new MemberResource($member), 'Data member baru berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return $this->error('Gagal menambah member: ' . $e->getMessage(), 'Bad Request', 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        if (! $member = Member::find($id)) {
            return $this->error('Data member tidak ditemukan!', 'Not Found', 404);
        }

        return $this->success(new MemberResource($member));
    }

    public function update(UpdateMemberAdminRequest $request, int $id): JsonResponse
    {
        if (! $member = Member::find($id)) {
            return $this->error('Data member tidak ditemukan!', 'Not Found', 404);
        }

        try {
            DB::transaction(function () use ($request, $member) {
                if ($userData = array_filter($request->only('username', 'password'))) {
                    $member->user?->update($userData);
                }
                $member->update($request->except('username', 'password'));
            });
        } catch (\Throwable $e) {
            return $this->error('Gagal update member: ' . $e->getMessage(), 'Bad Request', 400);
        }

        return $this->success(new MemberResource($member->fresh()), 'Data member berhasil diperbarui!');
    }

    public function destroy(int $id): JsonResponse
    {
        if (! $member = Member::find($id)) {
            return $this->error('Data member tidak ditemukan!', 'Not Found', 404);
        }

        try {
            DB::transaction(function () use ($member) {
                $member->user?->delete();
                $member->delete();
            });
        } catch (\Throwable $e) {
            return $this->error('Gagal menghapus member: ' . $e->getMessage(), 'Bad Request', 400);
        }

        return $this->success([
            'id' => (int) $id,
            'deleted' => true,
        ], 'Data member berhasil dihapus!');
    }
}
