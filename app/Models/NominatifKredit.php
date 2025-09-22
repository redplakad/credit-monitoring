<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NominatifKredit extends Model
{
    use HasFactory;

    protected $table = 'nominatif_kredit';

    protected $fillable = [
        'DATADATE',
        'CAB',
        'NOMOR_REKENING',
        'NO_CIF',
        'NAMA_NASABAH',
        'ALAMAT',
        'KODE_KOLEK',
        'JML_HARI_TUNGGAKAN',
        'KD_PRD',
        'KET_KD_PRD',
        'NOMOR_PERJANJIAN',
        'TGL_AWAL_FAS',
        'TGL_AKHIR_FAS',
        'PLAFOND_AWAL',
        'BGA',
        'TUNGGAKAN_POKOK',
        'TUNGGAKAN_BUNGA',
        'ANGSURAN_TOTAL',
        'NO_HP',
        'POKOK_PINJAMAN',
        'TITIPAN_EFEKTIF',
        'JANGKA_WAKTU',
        'REK_PENCAIRAN',
        'TGL_LAHIR',
        'NIK',
        'AO',
        'KELURAHAN',
        'KECAMATAN',
        'CADANGAN_PPAP',
        'TEMPAT_BEKERJA',
        'TGL_AKHIR_BAYAR',
        'JENIS_JAMINAN',
        'NILAI_LEGALITAS',
        'IMPORT_BY',
    ];

    protected function casts(): array
    {
        return [
            'JML_HARI_TUNGGAKAN' => 'integer',
            'PLAFOND_AWAL' => 'decimal:2',
            'BGA' => 'decimal:4',
            'TUNGGAKAN_POKOK' => 'decimal:2',
            'TUNGGAKAN_BUNGA' => 'decimal:2',
            'ANGSURAN_TOTAL' => 'decimal:2',
            'POKOK_PINJAMAN' => 'decimal:2',
            'TITIPAN_EFEKTIF' => 'decimal:2',
            'JANGKA_WAKTU' => 'integer',
            'CADANGAN_PPAP' => 'decimal:2',
            'NILAI_LEGALITAS' => 'decimal:2',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'CAB', 'branch_code');
    }

    public function collections()
    {
        return $this->hasMany(Collection::class, 'NOMOR_REKENING_DEBITUR', 'NOMOR_REKENING');
    }
}
