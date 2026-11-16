<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicMonitorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $recentLogs = $this->pingLogs()
            ->latest('checked_at')
            ->take(60)
            ->get()
            ->reverse()
            ->values();

        $totalLogs = $this->pingLogs()->count();
        $successLogs = $this->pingLogs()
            ->whereNull('error_message')
            ->whereBetween('status_code', [200, 299])
            ->count();

        $lastChecked = $this->last_checked_at ?? $this->updated_at;

        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'url'               => $this->url,
            'last_status'       => $this->last_status ?? 'pending',
            'last_response_ms'  => $this->last_response_ms,
            'last_checked_at'   => $lastChecked ? Carbon::parse($lastChecked)->diffForHumans() : null,
            'uptime_percentage' => $totalLogs > 0 ? round(($successLogs / $totalLogs) * 100, 1) : 100.0,
            'recent_pings'      => $recentLogs->map(fn ($log) => [
                'id'               => $log->id,
                'status'           => $log->error_message ? 'down' : ($log->response_time_ms > 2000 ? 'slow' : 'up'),
                'response_time_ms' => $log->response_time_ms,
                'checked_at'       => $log->checked_at ? Carbon::parse($log->checked_at)->format('d M H:i') : '-',
            ]),
        ];
    }
}