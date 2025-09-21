<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NominatifKredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NominatifKreditController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'nominatif_kredit:' . md5(json_encode($request->all()));
        $data = Cache::store('redis')->remember($cacheKey, 60, function () use ($request) {
            $query = NominatifKredit::query();

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('NOMOR_REKENING', 'like', "%{$search}%")
                      ->orWhere('NAMA_NASABAH', 'like', "%{$search}%")
                      ->orWhere('NO_CIF', 'like', "%{$search}%")
                      ->orWhere('AO', 'like', "%{$search}%");
                });
            }

            // Filter
            if ($request->filled('cabang')) {
                $query->where('CAB', $request->cabang);
            }
            if ($request->filled('ket_kd_prd')) {
                $query->where('KET_KD_PRD', $request->ket_kd_prd);
            }
            if ($request->filled('kode_kolek')) {
                $query->where('KODE_KOLEK', $request->kode_kolek);
            }
            if ($request->filled('ao')) {
                $query->where('AO', $request->ao);
            }

            // Sorting
            $allowedSort = [
                'POKOK_PINJAMAN', 'KODE_KOLEK', 'AO', 'KET_KD_PRD', 'TUNGGAKAN_POKOK', 'TUNGGAKAN_BUNGA'
            ];
            if ($request->filled('sort_by') && in_array($request->sort_by, $allowedSort)) {
                $query->orderBy($request->sort_by, $request->get('sort_order', 'asc'));
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination
            $perPage = min($request->get('per_page', 10), 100);
            return $query->paginate($perPage);
        });

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'from' => $data->firstItem(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'to' => $data->lastItem(),
                'total' => $data->total(),
            ],
            'links' => [
                'first' => $data->url(1),
                'last' => $data->url($data->lastPage()),
                'prev' => $data->previousPageUrl(),
                'next' => $data->nextPageUrl(),
            ]
        ]);
    }

    public function show($id)
    {
        $cacheKey = 'nominatif_kredit_detail:' . $id;
        $nominatif = Cache::store('redis')->remember($cacheKey, 60, function () use ($id) {
            return NominatifKredit::findOrFail($id);
        });
        return response()->json(['data' => $nominatif]);
    }

    public function getFilterOptions()
    {
        $cacheKey = 'nominatif_kredit_filter_options';
        $options = Cache::store('redis')->remember($cacheKey, 60, function () {
            return [
                'cabang' => NominatifKredit::distinct()->whereNotNull('CAB')->where('CAB', '!=', '')->pluck('CAB')->sort()->values(),
                'ket_kd_prd' => NominatifKredit::distinct()->whereNotNull('KET_KD_PRD')->where('KET_KD_PRD', '!=', '')->pluck('KET_KD_PRD')->sort()->values(),
                'kode_kolek' => NominatifKredit::distinct()->whereNotNull('KODE_KOLEK')->where('KODE_KOLEK', '!=', '')->pluck('KODE_KOLEK')->sort()->values(),
                'ao' => NominatifKredit::distinct()->whereNotNull('AO')->where('AO', '!=', '')->pluck('AO')->sort()->values(),
            ];
        });
        return response()->json($options);
    }
}
