<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;

class FilamentImageUpload
{
    public static function saveToPublicDisk(TemporaryUploadedFile $file, string $directory, ?string $preferredName = null): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');
        $targetDirectory = Storage::disk('public')->path($directory);

        if (! is_dir($targetDirectory) && ! mkdir($targetDirectory, 0755, true) && ! is_dir($targetDirectory)) {
            throw new RuntimeException("Folder storage/app/public/{$directory} tidak bisa dibuat.");
        }

        if (! is_writable($targetDirectory)) {
            throw new RuntimeException("Folder storage/app/public/{$directory} tidak bisa ditulis.");
        }

        $extension = self::resolveExtension($file);
        $baseName = self::resolveBaseName($preferredName);
        $filename = $baseName . '.' . $extension;
        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;
        $temporaryPath = $file->getRealPath() ?: $file->getPathname();

        self::copyTemporaryFile($temporaryPath, $targetPath);
        self::pruneLivewireTemporaryUploads();

        return $directory . '/' . $filename;
    }

    public static function saveToPublicPath(TemporaryUploadedFile $file, string $directory, string $filename): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');
        $targetDirectory = public_path($directory);

        if (! is_dir($targetDirectory) && ! mkdir($targetDirectory, 0755, true) && ! is_dir($targetDirectory)) {
            throw new RuntimeException("Folder public/{$directory} tidak bisa dibuat.");
        }

        if (! is_writable($targetDirectory)) {
            throw new RuntimeException("Folder public/{$directory} tidak bisa ditulis.");
        }

        $filename = basename($filename);
        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;
        $temporaryPath = $file->getRealPath() ?: $file->getPathname();

        self::copyTemporaryFile($temporaryPath, $targetPath);
        self::pruneLivewireTemporaryUploads();

        return $filename;
    }

    public static function deleteFromPublicDisk(mixed $path): void
    {
        $path = self::normalizePublicPath($path);

        if (! $path) {
            self::pruneLivewireTemporaryUploads();
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        self::pruneLivewireTemporaryUploads();
    }

    public static function pruneLivewireTemporaryUploads(int $olderThanMinutes = 30): void
    {
        $disk = config('livewire.temporary_file_upload.disk') ?: config('filesystems.default', 'local');
        $directory = trim((string) config('livewire.temporary_file_upload.directory', 'livewire-tmp'), '/');

        try {
            if (! Storage::disk($disk)->exists($directory)) {
                return;
            }

            $expiration = now()->subMinutes($olderThanMinutes)->timestamp;

            foreach (Storage::disk($disk)->allFiles($directory) as $file) {
                if (Storage::disk($disk)->lastModified($file) < $expiration) {
                    Storage::disk($disk)->delete($file);
                }
            }
        } catch (\Throwable) {
            // Cleanup temporary upload tidak boleh menggagalkan proses simpan form.
        }
    }

    private static function normalizePublicPath(mixed $value): ?string
    {
        if (is_array($value)) {
            foreach (array_reverse($value) as $item) {
                $path = self::normalizePublicPath($item);

                if ($path) {
                    return $path;
                }
            }

            return null;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $path = trim(str_replace('\\', '/', $value));
        $path = preg_replace('#^/?storage/#', '', $path) ?: $path;
        $path = preg_replace('#^/?public/#', '', $path) ?: $path;

        return ltrim($path, '/');
    }

    private static function copyTemporaryFile(?string $temporaryPath, string $targetPath): void
    {
        if (! $temporaryPath || ! is_file($temporaryPath)) {
            throw new RuntimeException('File temporary upload Livewire tidak ditemukan.');
        }

        $contents = file_get_contents($temporaryPath);

        if ($contents === false) {
            throw new RuntimeException('File temporary upload Livewire tidak bisa dibaca.');
        }

        if (file_put_contents($targetPath, $contents, LOCK_EX) === false) {
            throw new RuntimeException('File upload tidak bisa disimpan ke folder tujuan.');
        }

        @chmod($targetPath, 0644);
    }

    private static function resolveExtension(TemporaryUploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');

        return match ($extension) {
            'jpeg', 'jpg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            'svg', 'svgz' => 'svg',
            default => 'jpg',
        };
    }

    private static function resolveBaseName(?string $preferredName = null): string
    {
        $baseName = $preferredName
            ? pathinfo($preferredName, PATHINFO_FILENAME)
            : (string) Str::uuid();

        $baseName = Str::of($baseName)
            ->trim()
            ->replaceMatches('/[^A-Za-z0-9_-]/', '-')
            ->replaceMatches('/-+/', '-')
            ->trim('-')
            ->toString();

        return $baseName !== '' ? $baseName : (string) Str::uuid();
    }
}
