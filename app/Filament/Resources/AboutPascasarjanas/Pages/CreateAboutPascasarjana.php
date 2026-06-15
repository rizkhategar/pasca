<?php

namespace App\Filament\Resources\AboutPascasarjanas\Pages;

use App\Filament\Resources\AboutPascasarjanas\AboutPascasarjanaResource;
use Filament\Resources\Pages\Page;

class CreateAboutPascasarjana extends Page
{
    protected static string $resource = AboutPascasarjanaResource::class;

    /**
 * Halaman ini menggunakan custom Blade view agar proses upload gambar tidak memakai
 * FileUpload bawaan Filament/Livewire.
 *
 * FileUpload bawaan Filament menggunakan temporary upload Livewire yang pada beberapa
 * environment dapat stuck di "Uploading 100%" sehingga tombol Save tidak bisa digunakan.
 *
 * Custom view ini memakai form HTML native multipart/form-data, sehingga file diproses
 * langsung oleh controller ketika tombol Save ditekan. Cara ini lebih stabil untuk upload
 * gambar About Pascasarjana.
 */

    protected string $view = 'filament.resources.about-pascasarjanas.pages.create-about-pascasarjana';
}
