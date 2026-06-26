<?php

namespace App\Http\Controllers;

use App\Models\AboutPostgraduate;

class AboutController extends Controller
{
    public function index()
    {
        $tentang = AboutPostgraduate::query()
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if ($tentang) {
            $points = collect($tentang->points ?? [])
                ->map(function (array $point, int $index) use ($tentang): array {
                    if (! empty($point['icon'])) {
                        $point['icon'] = 'about-pascasarjanas/' . $tentang->id . '/point-icons/' . $index . '?v=' . optional($tentang->updated_at)->timestamp;
                    }

                    return $point;
                })
                ->values()
                ->all();

            $tentang->setAttribute('points', $points);

            if (! empty($tentang->direktur_image)) {
                $tentang->setAttribute(
                    'direktur_image',
                    'about-pascasarjanas/' . $tentang->id . '/director-image?v=' . optional($tentang->updated_at)->timestamp
                );

                if (empty($tentang->direktur_name) && empty($tentang->direktur_message)) {
                    $tentang->setAttribute('direktur_message', '<p></p>');
                }
            }
        }

        return view('profile.about', compact('tentang'));
    }
}
