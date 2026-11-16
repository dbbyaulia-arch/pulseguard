<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicMonitorResource;
use App\Models\Monitor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PublicStatusController extends Controller
{
    public function index(): JsonResponse
    {
        $monitors = Monitor::orderBy('name')->get();
        $lastRun = Cache::get('pulseguard_scheduler_last_run');

        return response()->json([
            'data' => PublicMonitorResource::collection($monitors),
            'meta' => [
                'last_updated'   => $lastRun ? Carbon::parse($lastRun)->diffForHumans() : 'Baru saja',
                'retention_days' => 14,
            ],
        ]);
    }
}