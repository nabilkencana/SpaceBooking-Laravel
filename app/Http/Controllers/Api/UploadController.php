<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    use ApiResponse;

    public function image(Request $request): JsonResponse
    {
        return $this->handleUpload($request, 'general', 'Upload gambar berhasil');
    }

    public function spaces(Request $request): JsonResponse
    {
        return $this->handleUpload($request, 'spaces', 'Foto space berhasil diupload');
    }

    public function members(Request $request): JsonResponse
    {
        return $this->handleUpload($request, 'members', 'Foto member berhasil diupload');
    }

    private function handleUpload(Request $request, string $folder, string $message): JsonResponse
    {
        $field = $request->hasFile('file') ? 'file' : ($request->hasFile('image') ? 'image' : 'foto');

        $request->validate([
            $field => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $file = $request->file($field);
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '-' . Str::random(10) . '.' . $extension;

        $disk = config('filesystems.default', 'public');
        $file->storeAs($folder, $filename, $disk);

        return $this->created([
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mimetype' => $file->getMimeType(),
            'size' => $file->getSize(),
            'url' => Storage::disk($disk)->url($folder . '/' . $filename),
        ], $message);
    }
}
