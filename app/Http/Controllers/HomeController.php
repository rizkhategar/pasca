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
            ->filter(function (Slider $slider): bool {
                $path = Slider::normalizeImagePath($slider->image_path);

                if (! $path || ! Storage::disk('public')->exists($path)) {
                    return false;
                }

                $slider->image_path = $path;

                return true;
            })
            ->values();

        return view('home', compact('sliders'));
    }
}
