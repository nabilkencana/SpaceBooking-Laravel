<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'statusCode' => 401,
                'message' => 'Unauthenticated',
                'error' => 'Unauthorized',
                'timestamp' => now()->toIso8601String(),
            ], 401);
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        return response()->json([
            'status' => false,
            'statusCode' => 403,
            'message' => 'Akses ditolak. Anda tidak memiliki hak akses yang diperlukan.',
            'error' => 'Forbidden',
            'timestamp' => now()->toIso8601String(),
        ], 403);
    }
}