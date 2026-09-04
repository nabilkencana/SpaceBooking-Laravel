<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SpaceDetailResource;
use App\Http\Resources\SpaceResource;
use App\Models\Space;
use App\Services\ReservasiService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SpaceController extends Controller
{
    use ApiResponse;

    protected array $spaceTypes = [
        [
            'tipe' => 'desk',
            'label' => 'Personal Desk',
            'deskripsi' => 'Meja kerja individual yang nyaman dengan fasilitas colokan listrik, WiFi kencang, dan air minum.',
        ],
        [
            'tipe' => 'meeting_room',
            'label' => 'Meeting Room',
            'deskripsi' => 'Ruang rapat tertutup dengan fasilitas proyektor/TV LED, whiteboard, sound system, dan AC dingin.',
        ],
        [
            'tipe' => 'private_office',
            'label' => 'Private Office',
            'deskripsi' => 'Ruang kantor privat eksklusif untuk tim kecil hingga menengah dengan akses fleksibel dan keamanan 24 jam.',
        ],
    ];

    public function __construct(protected ReservasiService $reservasiService)
    {
    }

    public function types(): JsonResponse
    {
        return $this->success($this->spaceTypes);
    }

    public function availability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_space' => ['required', 'integer', 'exists:spaces,id'],
            'tanggal' => ['required', 'date', 'date_format:Y-m-d'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'durasi_jam' => ['required', 'integer', 'min:1', 'max:24'],
        ]);

        $space = Space::findOrFail($validated['id_space']);
        $jamSelesai = $this->reservasiService->hitungJamSelesai($validated['jam_mulai'], $validated['durasi_jam']);

        try {
            $this->reservasiService->cekBentrok(
                $space->id,
                $validated['tanggal'],
                $validated['jam_mulai'],
                $jamSelesai
            );
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 'Bad Request', 400);
        }

        $estimasiTotal = $space->harga_per_jam * $validated['durasi_jam'];

        return $this->success([
            'available' => true,
            'id_space' => $space->id,
            'nama_space' => $space->nama_space,
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $jamSelesai,
            'durasi_jam' => (int) $validated['durasi_jam'],
            'harga_per_jam' => $space->harga_per_jam,
            'estimasi_total' => $estimasiTotal,
        ], 'Space tersedia untuk dipesan pada jadwal yang diminta');
    }

    public function index(Request $request): JsonResponse
    {
        $spaces = Space::with('owner')
            ->when($request->tipe, fn ($q, $tipe) => $q->where('tipe', $tipe))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($sub) =>
                $sub->where('nama_space', 'like', "%{$s}%")->orWhere('deskripsi', 'like', "%{$s}%")
            ))
            ->orderBy('tipe')
            ->orderBy('nama_space')
            ->get();

        return $this->success(SpaceResource::collection($spaces));
    }

    public function show(int $id): JsonResponse
    {
        if (! $space = Space::with('owner')->find($id)) {
            return $this->error('Space dengan ID tersebut tidak ditemukan!', 'Not Found', 404);
        }

        return $this->success(new SpaceDetailResource($space));
    }
}
