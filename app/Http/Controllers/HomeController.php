<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $sliders = Slider::query()
            ->where('is_active', true)
            ->whereNotNull('image_path')
            ->orderBy('sort_order')
            ->oldest('id')
            ->get()
            ->map(function (Slider $slider): Slider {
                $path = Slider::normalizeImagePath($slider->image_path);

                if ($path && Storage::disk('public')->exists($path)) {
                    $mime = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';
                    $image = Storage::disk('public')->get($path);

                    $slider->hero_image_url = 'data:' . $mime . ';base64,' . base64_encode($image);
                }

                return $slider;
            })
            ->filter(fn (Slider $slider): bool => ! empty($slider->hero_image_url))
            ->values();

        return view('home', compact('sliders'));
    }
}
