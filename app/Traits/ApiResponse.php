<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Berhasil memproses permintaan', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ], $statusCode);
    }

    protected function error(string $message = 'Terjadi kesalahan', string $error = 'Error', int $statusCode = 400, $data = null): JsonResponse
    {
        $response = [
            'status' => false,
            'statusCode' => $statusCode,
            'message' => $message,
            'error' => $error,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    protected function created($data = null, string $message = 'Data berhasil dibuat'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function noContent(string $message = 'Berhasil'): JsonResponse
    {
        return $this->success(null, $message, 204);
    }
}