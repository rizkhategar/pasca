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
            ->orderBy('sort_order')
            ->oldest('id')
            ->get();

        return view('home', compact('sliders'));
    }
}
