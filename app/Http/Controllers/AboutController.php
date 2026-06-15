<?php

namespace App\Http\Controllers;

use App\Models\AboutPascasarjana;

class AboutController extends Controller
{
    public function index()
    {
        $tentang = AboutPascasarjana::query()
            ->latest('updated_at')
            ->latest('id')
            ->first();

        return view('about', compact('tentang'));
    }
}
