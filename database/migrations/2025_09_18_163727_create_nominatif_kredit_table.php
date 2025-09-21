<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nominatif_kredit', function (Blueprint $table) {
            $table->id();
            $table->string('DATADATE');
            $table->string('CAB');
            $table->string('NOMOR_REKENING')->unique();
            $table->string('NO_CIF');
            $table->string('NAMA_NASABAH');
            $table->text('ALAMAT');
            $table->string('KODE_KOLEK');
            $table->integer('JML_HARI_TUNGGAKAN')->nullable();
            $table->string('KD_PRD');
            $table->string('KET_KD_PRD');
            $table->string('NOMOR_PERJANJIAN');
            $table->string('TGL_AWAL_FAS');
            $table->string('TGL_AKHIR_FAS');
            $table->decimal('PLAFOND_AWAL', 15, 2)->nullable();
            $table->decimal('BGA', 8, 4)->nullable();
            $table->decimal('TUNGGAKAN_POKOK', 15, 2)->nullable();
            $table->decimal('TUNGGAKAN_BUNGA', 15, 2)->nullable();
            $table->decimal('ANGSURAN_TOTAL', 15, 2)->nullable();
            $table->string('NO_HP')->nullable();
            $table->decimal('POKOK_PINJAMAN', 15, 2)->nullable();
            $table->decimal('TITIPAN_EFEKTIF', 15, 2)->nullable();
            $table->integer('JANGKA_WAKTU')->nullable();
            $table->string('REK_PENCAIRAN')->nullable();
            $table->string('TGL_LAHIR')->nullable();
            $table->string('NIK')->nullable();
            $table->string('AO')->nullable();
            $table->string('KELURAHAN')->nullable();
            $table->string('KECAMATAN')->nullable();
            $table->decimal('CADANGAN_PPAP', 15, 2)->nullable();
            $table->string('TEMPAT_BEKERJA')->nullable();
            $table->string('TGL_AKHIR_BAYAR')->nullable();
            $table->string('JENIS_JAMINAN')->nullable();
            $table->decimal('NILAI_LEGALITAS', 15, 2)->nullable();
            $table->unsignedBigInteger('IMPORT_BY')->nullable();
            $table->timestamps();
            
            $table->foreign('IMPORT_BY')->references('id')->on('users')->onDelete('set null');
            $table->index(['DATADATE', 'CAB']);
            $table->index(['NOMOR_REKENING']);
            $table->index(['NO_CIF']);
            $table->index(['KODE_KOLEK']);
            $table->index(['AO']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nominatif_kredit');
    }
};
