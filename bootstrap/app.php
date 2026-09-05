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

        $jsonErr = fn ($status, $msg, $err, $data = null) => response()->json(
            array_filter([
                'status' => false,
                'statusCode' => $status,
                'message' => $msg,
                'error' => $err,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ], fn ($v) => $v !== null),
            $status
        );

        $exceptions->render(fn (NotFoundHttpException $e) => $jsonErr(404, 'Resource tidak ditemukan', 'Not Found'));
        $exceptions->render(fn (AccessDeniedHttpException $e) => $jsonErr(403, 'Akses ditolak', 'Forbidden'));
        $exceptions->render(fn (AuthenticationException $e) => $jsonErr(401, 'Unauthenticated atau token tidak valid', 'Unauthorized'));
        $exceptions->render(fn (ValidationException $e) => $jsonErr(422, 'Validasi gagal', 'Validation Error', $e->errors()));
        $exceptions->render(fn (\Throwable $e) => $jsonErr(500, config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan server', 'Internal Server Error'));
    })->create();
