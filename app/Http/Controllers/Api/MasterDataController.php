<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NominatifKredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    // List unique DATADATE, count, sum(pokok_pinjaman), with search & pagination
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = min($request->input('per_page', 10), 100);
        $query = NominatifKredit::query();
        if ($search) {
            $query->where('DATADATE', 'like', "%$search%");
        }
        $data = $query->select('DATADATE',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(POKOK_PINJAMAN) as bakidebet')
            )
            ->groupBy('DATADATE')
            ->orderBy('DATADATE', 'desc')
            ->paginate($perPage);
        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
            ]
        ]);
    }

    // Delete all by DATADATE
    public function destroy($datadate)
    {
        $count = NominatifKredit::where('DATADATE', $datadate)->count();
        if ($count === 0) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        NominatifKredit::where('DATADATE', $datadate)->delete();
        return response()->json(['message' => "Berhasil menghapus $count data dengan DATADATE $datadate"]);
    }

    // Upload & import CSV ke NominatifKredit
    public function import(Request $request)
    {
        \Log::info('[IMPORT] Mulai proses import', [
            'ip' => $request->ip(),
            'user_id' => optional($request->user())->id,
            'datadate' => $request->input('datadate'),
            'has_file' => $request->hasFile('file'),
        ]);
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt',
                'datadate' => 'required|string|max:20',
            ]);
            \Log::info('[IMPORT] Validasi sukses', [
                'datadate' => $request->input('datadate'),
                'filename' => $request->file('file') ? $request->file('file')->getClientOriginalName() : null,
            ]);
            $file = $request->file('file');
            $datadate = $request->input('datadate');
            $path = $file->store('imports');
            \Log::info('[IMPORT] File disimpan', [
                'path' => $path,
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);
            $userId = optional($request->user())->id;
            \App\Jobs\ImportNominatifKreditJob::dispatch($path, $datadate, $userId);
            \Log::info('[IMPORT] Job dispatch sukses', [
                'path' => $path,
                'datadate' => $datadate,
            ]);
            return response()->json(['message' => 'File diupload, proses import berjalan di background.']);
        } catch (\Throwable $e) {
            \Log::error('[IMPORT] ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Gagal import: ' . $e->getMessage(),
            ], 500);
        }
    }
}
