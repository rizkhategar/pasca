<?php

namespace App\Http\Controllers;

use App\Models\VisiMisi;

class VisiMisiController extends Controller
{
    public function index()
    {
        $visiMisi = VisiMisi::first();

        return view('profil.visi-misi', compact('visiMisi'));
    }
}
