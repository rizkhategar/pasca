<?php

namespace App\Filament\Resources\AboutPascasarjanas\Pages;

use App\Filament\Resources\AboutPascasarjanas\AboutPascasarjanaResource;
use Filament\Resources\Pages\Page;

class CreateAboutPascasarjana extends Page
{
    protected static string $resource = AboutPascasarjanaResource::class;

    protected string $view = 'filament.resources.about-pascasarjanas.pages.create-about-pascasarjana';

    public function getTitle(): string
    {
        return 'Create About Pascasarjana';
    }
}
