<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PingLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = 'down';
        if ($this->error_message === null && $this->status_code >= 200 && $this->status_code < 300) {
            $status = $this->response_time_ms > 2000 ? 'slow' : 'up';
        }

        return [
            'id'               => $this->id,
            'status'           => $status,
            'status_code'      => $this->status_code,
            'response_time_ms' => $this->response_time_ms,
            'error_message'    => $this->error_message,
            'checked_at'       => $this->checked_at?->format('d M Y, H:i:s'),
            'checked_at_human' => $this->checked_at?->diffForHumans(),
        ];
    }
}