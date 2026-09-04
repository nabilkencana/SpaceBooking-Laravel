<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use App\Traits\ApiResponse;
use App\Traits\OwnedResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponse, OwnedResource;

    protected array $tipeLabels = [
        'desk' => 'Personal Desk',
        'meeting_room' => 'Meeting Room',
        'private_office' => 'Private Office',
    ];

    public function monthly(Request $request): JsonResponse
    {
        return $this->success($this->hitungLaporan($request));
    }

    public function income(Request $request): JsonResponse
    {
        $data = $this->hitungLaporan($request);

        return $this->success([
            'month' => $data['month'],
            'year' => $data['year'],
            'realisasi_pendapatan_bersih' => $data['realisasi_pendapatan_bersih'],
        ]);
    }

    private function hitungLaporan(Request $request): array
    {
        $owner = $this->getOwner($request->user()->id);
        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);

        $spaceIds = $owner ? $owner->spaces()->pluck('id') : collect();

        $query = Reservasi::with('space')
            ->whereIn('space_id', $spaceIds)
            ->whereMonth('tanggal_reservasi', $month)
            ->whereYear('tanggal_reservasi', $year);

        $semua = (clone $query)->get();
        $realisasi = (clone $query)->whereIn('status', ['aktif', 'selesai'])->get();
        $grouped = $realisasi->groupBy('space.tipe');

        $rincianPerTipe = collect($this->tipeLabels)->map(fn ($label, $tipe) => [
            'tipe' => $tipe,
            'label' => $label,
            'total_booking' => ($items = $grouped->get($tipe, collect()))->count(),
            'total_jam' => $items->sum('durasi_jam'),
            'total_pendapatan' => $items->sum('total_bayar'),
        ])->values()->all();

        return [
            'month' => $month,
            'year' => $year,
            'total_transaksi' => $semua->count(),
            'total_jam_terpakai' => $realisasi->sum('durasi_jam'),
            'estimasi_pendapatan_kotor' => $semua->sum('total_harga_awal'),
            'total_potongan_diskon' => $semua->sum('potongan_diskon'),
            'realisasi_pendapatan_bersih' => $realisasi->sum('total_bayar'),
            'rincian_per_tipe_space' => $rincianPerTipe,
        ];
    }
}
