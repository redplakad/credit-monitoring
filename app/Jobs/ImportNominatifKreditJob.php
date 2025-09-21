<?php

namespace App\Jobs;

use App\Models\NominatifKredit;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ImportNominatifKreditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $datadate;
    protected $userId;

    public function __construct($filePath, $datadate, $userId)
    {
        $this->filePath = $filePath;
        $this->datadate = $datadate;
        $this->userId = $userId;
    }

    public function handle()
    {
        $fullPath = storage_path('app/' . $this->filePath);
        if (!file_exists($fullPath)) {
            Log::error('Import CSV gagal: file tidak ditemukan ' . $fullPath);
            return;
        }
        $handle = fopen($fullPath, 'r');
        if (!$handle) {
            Log::error('Import CSV gagal: tidak bisa membuka file ' . $fullPath);
            return;
        }
    $header = fgetcsv($handle, 0, '|', '"', '\\');
        if (!$header) {
            Log::error('Import CSV gagal: header tidak ditemukan');
            fclose($handle);
            return;
        }
        // Mapping kolom CSV ke field model
        $map = [
            'CAB' => 'CAB',
            'NOMOR_REKENING' => 'NOMOR_REKENING',
            'NO_CIF' => 'NO_CIF',
            'NAMA_NASABAH' => 'NAMA_NASABAH',
            'ALAMAT' => 'ALAMAT',
            'KODE_KOLEK' => 'KODE_KOLEK',
            'JML_HARI_TUNGGAKAN' => 'JML_HARI_TUNGGAKAN',
            'KD_PRD' => 'KD_PRD',
            'KET_KD_PRD' => 'KET_KD_PRD',
            'NOMOR_PERJANJIAN' => 'NOMOR_PERJANJIAN',
            'TGL_AWAL_FAS' => 'TGL_AWAL_FAS',
            'TGL_AKHIR_FAS' => 'TGL_AKHIR_FAS',
            'PLAFOND_AWAL' => 'PLAFOND_AWAL',
            '%BGA' => 'BGA',
            'TUNGGAKAN_POKOK' => 'TUNGGAKAN_POKOK',
            'TUNGGAKAN_BUNGA' => 'TUNGGAKAN_BUNGA',
            'ANGSURAN_TOTAL' => 'ANGSURAN_TOTAL',
            'NO_HP' => 'NO_HP',
            'POKOK_PINJAMAN' => 'POKOK_PINJAMAN',
            'TITIPAN_EFEKTIF' => 'TITIPAN_EFEKTIF',
            'JANGKA_WAKTU' => 'JANGKA_WAKTU',
            'REK_PENCAIRAN' => 'REK_PENCAIRAN',
            'TGL_LAHIR' => 'TGL_LAHIR',
            'NIK' => 'NIK',
            'AO' => 'AO',
            'KELURAHAN' => 'KELURAHAN',
            'KECAMATAN' => 'KECAMATAN',
            'CADANGAN_PPAP' => 'CADANGAN_PPAP',
            'TEMPAT_BEKERJA' => 'TEMPAT_BEKERJA',
            'TGL_AKHIR_BAYAR' => 'TGL_AKHIR_BAYAR',
            'JENIS_JAMINAN' => 'JENIS_JAMINAN',
            'NILAI_LEGALITAS' => 'NILAI_LEGALITAS',
        ];
        $csvToModel = [];
        foreach ($header as $i => $col) {
            if (isset($map[$col])) {
                $csvToModel[$i] = $map[$col];
            }
        }
        $batch = [];
        $batchSize = 1000;
    while (($row = fgetcsv($handle, 0, '|', '"', '\\')) !== false) {
            $data = [];
            foreach ($csvToModel as $i => $field) {
                $data[$field] = $row[$i] ?? null;
            }
            $data['DATADATE'] = $this->datadate;
            // Validasi user id, jika tidak ada di tabel users, isi null
            $data['IMPORT_BY'] = User::where('id', $this->userId)->exists() ? $this->userId : null;
            $batch[] = $data;
            if (count($batch) >= $batchSize) {
                NominatifKredit::insert($batch);
                $batch = [];
            }
        }
        if (count($batch) > 0) {
            NominatifKredit::insert($batch);
        }
        fclose($handle);
        Log::info('Import CSV selesai: ' . $fullPath);
    }
}
