<?php

namespace App\Services;

use App\Models\Monitor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class UptimeService
{
    public function ping(Monitor $monitor): Monitor
    {
        $start = hrtime(true);

        $defaultHeaders = [
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,application/json,*/*;q=0.8',
            'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
        ];

        try {
            $headers = array_merge(
                $defaultHeaders,
                is_array($monitor->headers) ? $monitor->headers : []
            );

            $request = Http::connectTimeout(5)
                ->timeout(10)
                ->withHeaders($headers);

            $method = strtoupper($monitor->method ?? 'GET');
            $response = $request->send($method, $monitor->url);

            $latencyMs = (int) round((hrtime(true) - $start) / 1_000_000);
            $isSuccess = $response->successful();

            if ($isSuccess) {
                $status = $latencyMs > 2000 ? 'slow' : 'up';
                $errorMessage = null;
            } else {
                $status = 'down';
                $errorMessage = "HTTP Error {$response->status()}";
            }

            $monitor->pingLogs()->create([
                'status_code'      => $response->status(),
                'response_time_ms' => $latencyMs,
                'error_message'    => $errorMessage,
                'checked_at'       => now(),
            ]);

            $monitor->update([
                'last_status'      => $status,
                'last_response_ms' => $latencyMs,
            ]);
        } catch (ConnectionException|Throwable $e) {
            $latencyMs = (int) round((hrtime(true) - $start) / 1_000_000);

            $monitor->pingLogs()->create([
                'status_code'      => null,
                'response_time_ms' => $latencyMs,
                'error_message'    => $e->getMessage(),
                'checked_at'       => now(),
            ]);

            $monitor->update([
                'last_status'      => 'down',
                'last_response_ms' => $latencyMs,
            ]);
        }

        return $monitor->fresh(['latestLogs']);
    }
}