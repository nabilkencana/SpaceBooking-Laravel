<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // 404 Not Found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 404,
                    'message' => 'Resource tidak ditemukan',
                    'error' => 'Not Found',
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }
        });

        // 403 Forbidden
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 403,
                    'message' => 'Akses ditolak',
                    'error' => 'Forbidden',
                    'timestamp' => now()->toIso8601String(),
                ], 403);
            }
        });

        // 401 Unauthenticated
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 401,
                    'message' => 'Unauthenticated atau token tidak valid',
                    'error' => 'Unauthorized',
                    'timestamp' => now()->toIso8601String(),
                ], 401);
            }
        });

        // Validation Error (422) - override format default
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 422,
                    'message' => 'Validasi gagal',
                    'error' => 'Validation Error',
                    'data' => $e->errors(),
                    'timestamp' => now()->toIso8601String(),
                ], 422);
            }
        });

        // 500 Internal Server Error
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $message = config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan server';
                return response()->json([
                    'status' => false,
                    'statusCode' => 500,
                    'message' => $message,
                    'error' => 'Internal Server Error',
                    'timestamp' => now()->toIso8601String(),
                ], 500);
            }
        });
    })->create();
