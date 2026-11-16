<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MonitorResource;
use App\Models\Monitor;
use App\Models\PingLog;
use App\Services\UptimeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminMonitorController extends Controller
{
    public function index(): JsonResponse
    {
        $monitors = Monitor::latest()->get();
        $lastRun = Cache::get('pulseguard_scheduler_last_run');
        $schedulerActive = $lastRun && Carbon::parse($lastRun)->gt(now()->subMinutes(2));

        $dbPath = database_path('database.sqlite');
        $dbSizeMb = file_exists($dbPath) ? round(filesize($dbPath) / 1024 / 1024, 2) : 0;
        $totalLogs = PingLog::count();

        return response()->json([
            'data'   => MonitorResource::collection($monitors),
            'system' => [
                'scheduler_active'   => $schedulerActive,
                'last_scheduler_run' => $lastRun ? Carbon::parse($lastRun)->diffForHumans() : 'Belum aktif',
                'retention_days'     => 14,
                'db_size_mb'         => $dbSizeMb,
                'total_logs'         => $totalLogs,
            ],
        ]);
    }

    public function store(Request $request, UptimeService $uptimeService): JsonResponse
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:100'],
            'url'    => ['required', 'url', 'max:500'],
            'method' => ['required', 'in:GET,HEAD,POST'],
        ]);

        $monitor = Monitor::create($validated);
        $uptimeService->ping($monitor);

        return response()->json([
            'data' => new MonitorResource($monitor->fresh()),
        ], 201);
    }

    public function ping(Monitor $monitor, UptimeService $uptimeService): JsonResponse
    {
        $updated = $uptimeService->ping($monitor);

        return response()->json([
            'data' => new MonitorResource($updated),
        ]);
    }

    public function pingAll(UptimeService $uptimeService): JsonResponse
    {
        $monitors = Monitor::all();
        foreach ($monitors as $monitor) {
            $uptimeService->ping($monitor);
        }

        return $this->index();
    }

    public function logs(Monitor $monitor): JsonResponse
    {
        $logs = $monitor->pingLogs()
            ->latest('checked_at')
            ->take(100)
            ->get()
            ->map(fn ($log) => [
                'id'               => $log->id,
                'status'           => $log->error_message ? 'down' : ($log->response_time_ms > 2000 ? 'slow' : 'up'),
                'status_code'      => $log->status_code,
                'response_time_ms' => $log->response_time_ms,
                'error_message'    => $log->error_message,
                'checked_at'       => $log->checked_at ? Carbon::parse($log->checked_at)->format('d M Y, H:i:s') : '-',
            ]);

        return response()->json(['data' => $logs]);
    }

    public function destroy(Monitor $monitor): JsonResponse
    {
        $monitor->delete();

        return response()->json(['message' => 'Monitor berhasil dihapus.']);
    }
}