<?php

namespace App\Http\Controllers;

use App\Models\VisionMission;

class VisiMisiController extends Controller
{
    public function index()
    {
        $visionMission = VisionMission::first();
        $visiMisi = $visionMission;

        return view('profile.vision-mission', compact('visionMission', 'visiMisi'));
    }
}
