<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReservasiStatusRequest;
use App\Http\Resources\ReservasiResource;
use App\Models\Reservasi;
use App\Services\ReservasiService;
use App\Traits\ApiResponse;
use App\Traits\OwnedResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ReservasiAdminController extends Controller
{
    use ApiResponse, OwnedResource;

    public function __construct(protected ReservasiService $reservasiService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if (! $owner = $this->getOwner($request->user()->id)) {
            return $this->error('Data profil coworking space tidak ditemukan.', 'Not Found', 404);
        }

        $reservasis = Reservasi::whereIn('space_id', $owner->spaces()->pluck('id'))
            ->with(['space', 'member'])
            ->when($request->month, fn ($q, $m) => $q->whereMonth('tanggal_reservasi', (int) $m))
            ->when($request->year, fn ($q, $y) => $q->whereYear('tanggal_reservasi', (int) $y))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->id_space, fn ($q, $id) => $q->where('space_id', (int) $id))
            ->when($request->tanggal, fn ($q, $d) => $q->whereDate('tanggal_reservasi', $d))
            ->orderByDesc('tanggal_reservasi')
            ->orderByDesc('jam_mulai')
            ->get();

        return $this->success(ReservasiResource::collection($reservasis));
    }

    public function updateStatus(UpdateReservasiStatusRequest $request, int $id): JsonResponse
    {
        $reservasi = $this->getOwnedReservasi($request->user()->id, $id);

        if (! $reservasi) {
            return $this->error('Reservasi tidak ditemukan atau bukan untuk space Anda.', 'Not Found', 404);
        }

        try {
            $this->reservasiService->validasiTransisi($reservasi->status, $request->status, 'admin');
            $reservasi->update(['status' => $request->status]);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 'Bad Request', 400);
        }

        return $this->success([
            'id' => $reservasi->id,
            'status' => $reservasi->status,
            'updated_at' => $reservasi->updated_at?->toIso8601String(),
        ], 'Status reservasi berhasil diperbarui menjadi ' . $reservasi->status);
    }

    public function checkIn(Request $request, int $id): JsonResponse
    {
        $reservasi = $this->getOwnedReservasi($request->user()->id, $id);

        if (! $reservasi) {
            return $this->error('Reservasi tidak ditemukan atau bukan untuk space Anda.', 'Not Found', 404);
        }

        try {
            $this->reservasiService->validasiTransisi($reservasi->status, 'aktif', 'checkin');
            $now = Carbon::now();
            $reservasi->update([
                'status' => 'aktif',
                'check_in_at' => $now,
            ]);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 'Bad Request', 400);
        }

        return $this->success([
            'id' => $reservasi->id,
            'status' => $reservasi->status,
            'check_in_time' => $now->toIso8601String(),
        ], 'Check-in member berhasil! Status reservasi aktif.');
    }

    public function checkOut(Request $request, int $id): JsonResponse
    {
        $reservasi = $this->getOwnedReservasi($request->user()->id, $id);

        if (! $reservasi) {
            return $this->error('Reservasi tidak ditemukan atau bukan untuk space Anda.', 'Not Found', 404);
        }

        try {
            $this->reservasiService->validasiTransisi($reservasi->status, 'selesai', 'checkout');
            $now = Carbon::now();
            $reservasi->update([
                'status' => 'selesai',
                'check_out_at' => $now,
            ]);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 'Bad Request', 400);
        }

        return $this->success([
            'id' => $reservasi->id,
            'status' => $reservasi->status,
            'check_out_time' => $now->toIso8601String(),
        ], 'Check-out member berhasil! Reservasi telah selesai.');
    }
}
