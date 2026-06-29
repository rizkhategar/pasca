@if (isset($sliders) && $sliders->count() > 0)
    @php
        $sliderItems = $sliders
            ->map(
                fn($slider) => [
                    'title' => $slider->title,
                    'subtitle' => $slider->subtitle,
                    'duration' => (int) ($slider->duration_ms ?? 3000),
                ],
            )
            ->values();
    @endphp

    <div id="pascaHeroSlidersData" hidden data-sliders='@json($sliderItems)'></div>
@endif
