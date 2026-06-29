<?php

namespace App\Http\Controllers;

use App\Models\Slider;
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
            ->filter(fn (Slider $slider): bool => (bool) $slider->resolved_image_file_path)
            ->values();

        return view('home', compact('sliders'));
    }
}
