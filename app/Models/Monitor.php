<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monitor extends Model
{
    protected $fillable = [
        'name',
        'url',
        'method',
        'headers',
        'last_status',
        'last_response_ms',
    ];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
        ];
    }

    public function pingLogs(): HasMany
    {
        return $this->hasMany(PingLog::class);
    }

    public function latestLogs(): HasMany
    {
        return $this->hasMany(PingLog::class)
            ->orderByDesc('checked_at')
            ->limit(10);
    }
    // Tambahkan method ini untuk menghitung persentase uptime berdasarkan log ping
public function calculateUptime(): float
{
    $total = $this->pingLogs()->count();

    if ($total === 0) {
        return 100.0;
    }

    $healthy = $this->pingLogs()
        ->whereNull('error_message')
        ->whereBetween('status_code', [200, 299])
        ->count();

    return round(($healthy / $total) * 100, 2);
}
}