<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = (string) env('ADMIN_PASSCODE', '');
        $providedKey = (string) $request->header('X-Admin-Key', '');

        if ($expectedKey === '' || ! hash_equals($expectedKey, $providedKey)) {
            return response()->json([
                'message' => 'Akses ditolak. Passcode admin tidak valid atau belum diisi.',
            ], 401);
        }

        return $next($request);
    }
}