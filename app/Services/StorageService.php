<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    /**
     * Get the active default storage disk.
     */
    public function getDiskName(): string
    {
        return config('filesystems.default', 'public');
    }

    /**
     * Store an uploaded file safely using Laravel Storage abstraction.
     *
     * @param UploadedFile $file
     * @param string $directory e.g. "products/123/images"
     * @param string|null $customFilename
     * @param string $visibility "public" or "private"
     * @return string|null Relative object path stored in DB
     */
    public function upload(UploadedFile $file, string $directory, ?string $customFilename = null, string $visibility = 'public'): ?string
    {
        if (!$file->isValid()) {
            Log::error('StorageService upload failed: Invalid file provided.');
            return null;
        }

        $disk = $this->getDiskName();
        $filename = $customFilename ?? (Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension());
        
        $path = trim($directory, '/') . '/' . $filename;

        try {
            $storedPath = Storage::disk($disk)->putFileAs($directory, $file, $filename, [
                'visibility' => $visibility,
            ]);

            return $storedPath ?: $path;
        } catch (\Throwable $e) {
            Log::error('StorageService upload exception: ' . $e->getMessage(), ['directory' => $directory, 'file' => $filename]);
            return null;
        }
    }

    /**
     * Delete a file from the configured storage disk.
     *
     * @param string|null $path
     * @return bool
     */
    public function delete(?string $path): bool
    {
        if (empty($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return false;
        }

        $disk = $this->getDiskName();

        try {
            $cleanPath = ltrim($path, '/');

            // Handle legacy local uploads starting with "uploads/"
            if (str_starts_with($cleanPath, 'uploads/')) {
                $publicFile = public_path($cleanPath);
                if (file_exists($publicFile)) {
                    @unlink($publicFile);
                }
            }

            if (Storage::disk($disk)->exists($cleanPath)) {
                return Storage::disk($disk)->delete($cleanPath);
            }
        } catch (\Throwable $e) {
            Log::error('StorageService delete exception: ' . $e->getMessage(), ['path' => $path]);
        }

        return false;
    }

    /**
     * Resolve the public or signed URL for a stored file path.
     *
     * @param string|null $path
     * @param string $fallback
     * @return string
     */
    public function url(?string $path, string $fallback = '/images/placeholder.png'): string
    {
        if (empty($path)) {
            return asset(ltrim($fallback, '/'));
        }

        // External URLs
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $disk = $this->getDiskName();
        $cleanPath = ltrim($path, '/');

        // Handle legacy local uploads (/uploads/products/...)
        if (str_starts_with($cleanPath, 'uploads/')) {
            if (file_exists(public_path($cleanPath))) {
                return asset($cleanPath);
            }
        }

        try {
            return Storage::disk($disk)->url($cleanPath);
        } catch (\Throwable $e) {
            Log::error('StorageService URL generation failed: ' . $e->getMessage(), ['path' => $path]);
            return asset(ltrim($fallback, '/'));
        }
    }
}
