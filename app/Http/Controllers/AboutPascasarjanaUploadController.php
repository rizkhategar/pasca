<?php

namespace App\Http\Controllers;

use App\Filament\Resources\AboutPascasarjanas\AboutPascasarjanaResource;
use App\Models\AboutPascasarjana;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AboutPascasarjanaUploadController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        AboutPascasarjana::create($this->makeData($request, $validated));

        return redirect()
            ->to(AboutPascasarjanaResource::getUrl('index'))
            ->with('success', 'About Pascasarjana berhasil dibuat.');
    }

    public function update(Request $request, AboutPascasarjana $aboutPascasarjana): RedirectResponse
    {
        $validated = $this->validateData($request);

        $aboutPascasarjana->update($this->makeData($request, $validated, $aboutPascasarjana));

        return redirect()
            ->to(AboutPascasarjanaResource::getUrl('index'))
            ->with('success', 'About Pascasarjana berhasil diperbarui.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'subheading' => ['required', 'string', 'max:255'],
            'heading' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'points' => ['nullable', 'array'],
            'points.*.title' => ['nullable', 'string', 'max:255'],
            'points.*.description' => ['nullable', 'string'],
            'points.*.existing_icon' => ['nullable', 'string'],
            'points.*.icon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'direktur_heading' => ['nullable', 'string', 'max:255'],
            'direktur_greeting' => ['nullable', 'string', 'max:255'],
            'direktur_name' => ['nullable', 'string', 'max:255'],
            'direktur_title' => ['nullable', 'string', 'max:255'],
            'direktur_message' => ['nullable', 'string'],
            'existing_direktur_image' => ['nullable', 'string'],
            'direktur_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    private function makeData(Request $request, array $validated, ?AboutPascasarjana $record = null): array
    {
        $oldPoints = collect($record?->points ?? []);

        $directorImage = AboutPascasarjana::normalizeImagePath($record?->direktur_image);

        if ($request->hasFile('direktur_image')) {
            $this->deleteFile($directorImage);
            $directorImage = $this->storeFile($request->file('direktur_image'), 'direktur-images', 'direktur');
        }

        return [
            'subheading' => $validated['subheading'],
            'heading' => $validated['heading'],
            'description' => $validated['description'],
            'points' => $this->makePoints($request, $oldPoints),
            'direktur_heading' => $validated['direktur_heading'] ?? null,
            'direktur_greeting' => $validated['direktur_greeting'] ?? null,
            'direktur_name' => $validated['direktur_name'] ?? null,
            'direktur_title' => $validated['direktur_title'] ?? null,
            'direktur_message' => $this->formatMessage($validated['direktur_message'] ?? null),
            'direktur_image' => $directorImage,
        ];
    }

    private function makePoints(Request $request, $oldPoints): array
    {
        $points = [];
        $keptIcons = [];

        foreach ($request->input('points', []) as $index => $point) {
            $title = trim((string) ($point['title'] ?? ''));
            $description = trim((string) ($point['description'] ?? ''));
            $icon = AboutPascasarjana::normalizeImagePath($point['existing_icon'] ?? data_get($oldPoints, $index . '.icon'));

            if ($request->hasFile("points.{$index}.icon")) {
                $this->deleteFile($icon);
                $icon = $this->storeFile($request->file("points.{$index}.icon"), 'tentang-icons', 'about-icon');
            }

            if ($title === '' && $description === '' && ! $icon) {
                continue;
            }

            if ($icon) {
                $keptIcons[] = $icon;
            }

            $points[] = [
                'title' => $title,
                'description' => $description,
                'icon' => $icon,
            ];
        }

        $oldPoints
            ->pluck('icon')
            ->map(fn ($path) => AboutPascasarjana::normalizeImagePath($path))
            ->filter()
            ->reject(fn ($path) => in_array($path, $keptIcons, true))
            ->each(fn ($path) => $this->deleteFile($path));

        return $points;
    }

    private function storeFile($file, string $directory, string $prefix): string
    {
        return $file->storeAs(
            $directory,
            $prefix . '-' . now()->format('YmdHis') . '-' . Str::random(8) . '.' . strtolower($file->getClientOriginalExtension() ?: 'jpg'),
            'public'
        );
    }

    private function deleteFile(?string $path): void
    {
        $path = AboutPascasarjana::normalizeImagePath($path);

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function formatMessage(?string $message): ?string
    {
        $message = trim((string) $message);

        if ($message === '') {
            return null;
        }

        return collect(preg_split('/\R{2,}/', $message))
            ->map(fn ($paragraph) => '<p>' . nl2br(e(trim($paragraph))) . '</p>')
            ->implode('');
    }
}
