<?php

namespace App\Filament\Resources\AboutPascasarjanas\Pages;

use App\Filament\Resources\AboutPascasarjanas\AboutPascasarjanaResource;
use App\Models\AboutPascasarjana;
use Filament\Resources\Pages\Page;

class EditAboutPascasarjana extends Page
{
    protected static string $resource = AboutPascasarjanaResource::class;

    protected string $view = 'filament.resources.about-pascasarjanas.pages.edit-about-pascasarjana';

    public AboutPascasarjana $record;

    public function mount(AboutPascasarjana $record): void
    {
        $this->record = $record;
    }

    public function getTitle(): string
    {
        return 'Edit About Pascasarjana';
    }
}
