@if (isset($sliders) && $sliders->count() > 0)
    @php
        $sliderItems = $sliders
            ->map(
                fn($slider) => [
                    'title' => $slider->title,
                    'subtitle' => $slider->subtitle,
                    'image' => route('sliders.image', $slider) . '?v=' . optional($slider->updated_at)->timestamp,
                    'duration' => (int) ($slider->duration_ms ?? 3000),
                ],
            )
            ->values();
    @endphp

    <div id="pascaHeroSlidersData" hidden data-sliders='@json($sliderItems)'></div>
@endif
