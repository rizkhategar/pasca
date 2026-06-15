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
        $validated = $this->validateRequest($request, true);

        $data = $this->makeData($request, $validated);

        AboutPascasarjana::create($data);

        return redirect()
            ->to(AboutPascasarjanaResource::getUrl('index'))
            ->with('success', 'About Pascasarjana has been created.');
    }

    public function update(Request $request, AboutPascasarjana $aboutPascasarjana): RedirectResponse
    {
        $validated = $this->validateRequest($request, false);

        $data = $this->makeData($request, $validated, $aboutPascasarjana);

        $aboutPascasarjana->update($data);

        return redirect()
            ->to(AboutPascasarjanaResource::getUrl('index'))
            ->with('success', 'About Pascasarjana has been updated.');
    }

    private function validateRequest(Request $request, bool $isCreate): array
    {
        return $request->validate([
            'subheading' => ['required', 'string', 'max:255'],
            'heading' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'points' => ['nullable', 'array'],
            'points.*.title' => ['nullable', 'string', 'max:255'],
            'points.*.description' => ['nullable', 'string'],
            'points.*.existing_icon' => ['nullable', 'string'],
            'points.*.icon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'direktur_heading' => ['nullable', 'string', 'max:255'],
            'direktur_greeting' => ['nullable', 'string', 'max:255'],
            'direktur_name' => ['nullable', 'string', 'max:255'],
            'direktur_title' => ['nullable', 'string', 'max:255'],
            'direktur_message' => ['nullable', 'string'],
            'existing_direktur_image' => ['nullable', 'string'],
            'direktur_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function makeData(Request $request, array $validated, ?AboutPascasarjana $record = null): array
    {
        $oldPoints = collect($record?->points ?? []);
        $points = $this->makePoints($request, $oldPoints);

        $direkturImage = $record?->direktur_image;

        if ($request->hasFile('direktur_image')) {
            $this->deleteIfExists($direkturImage);
            $direkturImage = $request->file('direktur_image')->storeAs(
                'direktur-images',
                $this->makeFileName('direktur', $request->file('direktur_image')->getClientOriginalExtension()),
                'public'
            );
        }

        return [
            'subheading' => $validated['subheading'],
            'heading' => $validated['heading'],
            'description' => $validated['description'],
            'points' => $points,
            'direktur_heading' => $validated['direktur_heading'] ?? null,
            'direktur_greeting' => $validated['direktur_greeting'] ?? null,
            'direktur_name' => $validated['direktur_name'] ?? null,
            'direktur_title' => $validated['direktur_title'] ?? null,
            'direktur_message' => $this->formatMessage($validated['direktur_message'] ?? ''),
            'direktur_image' => $direkturImage,
        ];
    }

    private function makePoints(Request $request, $oldPoints): array
    {
        $pointsInput = $request->input('points', []);
        $points = [];
        $keptIcons = [];

        foreach ($pointsInput as $index => $point) {
            $title = trim((string) ($point['title'] ?? ''));
            $description = trim((string) ($point['description'] ?? ''));
            $icon = $point['existing_icon'] ?? null;

            if ($request->hasFile("points.{$index}.icon")) {
                $this->deleteIfExists($icon);
                $uploadedIcon = $request->file("points.{$index}.icon");
                $icon = $uploadedIcon->storeAs(
                    'tentang-icons',
                    $this->makeFileName('about-icon', $uploadedIcon->getClientOriginalExtension()),
                    'public'
                );
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
            ->filter()
            ->reject(fn ($icon) => in_array($icon, $keptIcons, true))
            ->each(fn ($icon) => $this->deleteIfExists($icon));

        return $points;
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

    private function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function makeFileName(string $prefix, string $extension): string
    {
        return $prefix . '-' . now()->format('YmdHis') . '-' . Str::random(8) . '.' . strtolower($extension);
    }
}
