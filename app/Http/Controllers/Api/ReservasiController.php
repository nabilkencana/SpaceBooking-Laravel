<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservasi\CreateReservasiRequest;
use App\Http\Resources\ETicketResource;
use App\Http\Resources\ReservasiDetailResource;
use App\Http\Resources\ReservasiResource;
use App\Models\Reservasi;
use App\Models\Space;
use App\Services\ReservasiService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ReservasiController extends Controller
{
    use ApiResponse;

    public function __construct(protected ReservasiService $reservasiService)
    {
    }

    public function store(CreateReservasiRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'member') {
            return $this->error('Hanya member yang dapat membuat reservasi.', 'Forbidden', 403);
        }

        if (! $member = $user->member) {
            return $this->error('Data member tidak ditemukan. Silakan lengkapi profil member.', 'Bad Request', 400);
        }

        if (! $space = Space::find($request->id_space)) {
            return $this->error('Space tidak ditemukan!', 'Not Found', 404);
        }

        try {
            $diskon = $this->reservasiService->resolveDiskon($request->id_diskon, $request->kode_promo);
            $jamSelesai = $this->reservasiService->hitungJamSelesai($request->jam_mulai, $request->durasi_jam);

            $this->reservasiService->cekBentrok(
                $space->id,
                $request->tanggal_reservasi,
                $request->jam_mulai,
                $jamSelesai
            );

            $harga = $this->reservasiService->hitungHarga($space, $request->durasi_jam, $diskon);
            $kodeBooking = $this->reservasiService->generateKodeBooking($request->tanggal_reservasi);

            $reservasi = Reservasi::create([
                'kode_booking' => $kodeBooking,
                'member_id' => $member->id,
                'space_id' => $space->id,
                'diskon_id' => $harga['diskon_id'],
                'tanggal_reservasi' => $request->tanggal_reservasi,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $jamSelesai,
                'durasi_jam' => $request->durasi_jam,
                'harga_per_jam' => $harga['harga_per_jam'],
                'total_harga_awal' => $harga['total_harga_awal'],
                'potongan_diskon' => $harga['potongan_diskon'],
                'total_bayar' => $harga['total_bayar'],
                'status' => 'belum_dikonfirm',
            ]);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 'Bad Request', 400);
        }

        return $this->created(
            (new ReservasiResource($reservasi->load(['space', 'member'])))->resolve(),
            'Reservasi berhasil dibuat! Silakan tunggu konfirmasi admin.'
        );
    }

    public function my(Request $request): JsonResponse
    {
        if (! $member = $request->user()->member) {
            return $this->error('Data member tidak ditemukan.', 'Bad Request', 400);
        }

        $reservasis = $member->reservasis()
            ->with('space')
            ->orderByDesc('tanggal_reservasi')
            ->orderByDesc('jam_mulai')
            ->get();

        return $this->success(ReservasiResource::collection($reservasis));
    }

    public function history(Request $request): JsonResponse
    {
        if (! $member = $request->user()->member) {
            return $this->error('Data member tidak ditemukan.', 'Bad Request', 400);
        }

        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);

        $items = $member->reservasis()
            ->whereMonth('tanggal_reservasi', $month)
            ->whereYear('tanggal_reservasi', $year)
            ->with('space')
            ->orderBy('tanggal_reservasi')
            ->get();

        return $this->success([
            'month' => $month,
            'year' => $year,
            'total_reservasi' => $items->count(),
            'total_pengeluaran' => $items->sum('total_bayar'),
            'items' => $items->map(fn ($r) => [
                'id' => $r->id,
                'kode_booking' => $r->kode_booking,
                'tanggal_reservasi' => Carbon::parse($r->tanggal_reservasi)->format('Y-m-d'),
                'jam_mulai' => $r->jam_mulai,
                'jam_selesai' => $r->jam_selesai,
                'durasi_jam' => $r->durasi_jam,
                'total_bayar' => $r->total_bayar,
                'status' => $r->status,
                'space_name' => $r->space?->nama_space,
            ]),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $reservasi = Reservasi::with(['space.owner', 'member', 'diskon'])->find($id);

        if (! $reservasi) {
            return $this->error('Reservasi tidak ditemukan!', 'Not Found', 404);
        }

        if (! $this->bolehAkses($request->user(), $reservasi)) {
            return $this->error('Anda tidak memiliki akses ke reservasi ini.', 'Forbidden', 403);
        }

        return $this->success(new ReservasiDetailResource($reservasi));
    }

    public function eTicket(Request $request, int $id): JsonResponse
    {
        $reservasi = Reservasi::with(['space.owner', 'member.user', 'diskon'])->find($id);

        if (! $reservasi) {
            return $this->error('Reservasi tidak ditemukan!', 'Not Found', 404);
        }

        if (! $this->bolehAkses($request->user(), $reservasi)) {
            return $this->error('Anda tidak memiliki akses ke e-ticket reservasi ini.', 'Forbidden', 403);
        }

        return $this->success(new ETicketResource($reservasi), 'E-Ticket berhasil dimuat');
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        if (! $reservasi = Reservasi::find($id)) {
            return $this->error('Reservasi tidak ditemukan!', 'Not Found', 404);
        }

        if ($reservasi->member_id !== $request->user()->member?->id) {
            return $this->error('Anda hanya dapat membatalkan reservasi milik sendiri.', 'Forbidden', 403);
        }

        try {
            $this->reservasiService->validasiTransisi($reservasi->status, 'dibatalkan', 'member');
            $reservasi->update(['status' => 'dibatalkan']);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 'Bad Request', 400);
        }

        return $this->success([
            'id' => $reservasi->id,
            'status' => $reservasi->status,
            'updated_at' => $reservasi->updated_at?->toIso8601String(),
        ], 'Reservasi berhasil dibatalkan oleh pengguna');
    }

    private function bolehAkses($user, Reservasi $reservasi): bool
    {
        return match ($user->role) {
            'member' => $reservasi->member_id === $user->member?->id,
            'admin_space' => $reservasi->space?->owner_id === $user->spaceOwner?->id,
            default => false,
        };
    }
}
