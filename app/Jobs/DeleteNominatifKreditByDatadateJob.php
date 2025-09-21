<?php

namespace App\Jobs;

use App\Models\NominatifKredit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteNominatifKreditByDatadateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $datadate;

    public function __construct($datadate)
    {
        $this->datadate = $datadate;
    }

    public function handle()
    {
        $count = NominatifKredit::where('DATADATE', $this->datadate)->count();
        NominatifKredit::where('DATADATE', $this->datadate)->delete();
        Log::info("[DELETE] Berhasil menghapus $count data dengan DATADATE {$this->datadate}");
        // Clear cache nominatif_kredit agar data terbaru langsung tampil
        try {
            \Cache::store('redis')->forget('nominatif_kredit:latest_datadate');
            $redis = \Cache::store('redis')->getRedis();
            $keys = $redis->keys('nominatif_kredit:*');
            foreach ($keys as $key) {
                $redis->del($key);
            }
        } catch (\Throwable $e) {
            Log::error('Gagal clear cache nominatif_kredit setelah delete: ' . $e->getMessage());
        }
    }
}
