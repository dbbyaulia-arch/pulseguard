<?php

namespace App\Console\Commands;

use App\Models\Monitor;
use App\Services\UptimeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PingMonitorsCommand extends Command
{
    protected $signature = 'monitors:ping';

    protected $description = 'Melakukan pengecekan HTTP/HTTPS ke seluruh layanan yang terdaftar';

    public function handle(UptimeService $uptimeService): int
    {
        // Catat jejak waktu eksekusi scheduler untuk indikator status di UI
        Cache::put('pulseguard_scheduler_last_run', now()->toIso8601String(), now()->addMinutes(5));

        $monitors = Monitor::all();

        if ($monitors->isEmpty()) {
            $this->info('Tidak ada layanan yang terdaftar untuk dipantau.');
            return self::SUCCESS;
        }

        $this->info("Memulai pengecekan {$monitors->count()} layanan...");

        foreach ($monitors as $monitor) {
            $updated = $uptimeService->ping($monitor);
            $status = strtoupper($updated->last_status ?? 'UNKNOWN');
            $ms = $updated->last_response_ms ?? 0;

            $this->line("- [{$status}] {$monitor->name} ({$monitor->url}) — {$ms} ms");
        }

        $this->info('Seluruh pengecekan selesai.');
        return self::SUCCESS;
    }
}