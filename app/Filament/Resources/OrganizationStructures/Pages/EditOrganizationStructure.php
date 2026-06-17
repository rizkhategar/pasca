<?php

namespace App\Filament\Resources\OrganizationStructures\Pages;

use App\Filament\Resources\OrganizationStructures\OrganizationStructureResource;
use App\Models\OrganizationStructure;
use Filament\Resources\Pages\Page;

class EditOrganizationStructure extends Page
{
    protected static string $resource = OrganizationStructureResource::class;

    /**
     * Halaman Edit Organization Structure sengaja menggunakan custom Blade view,
     * bukan default Filament form.
     *
     * Alasan:
     * - Upload gambar pada halaman Struktur Organisasi membutuhkan proses yang stabil
     *   dan cepat karena file gambar langsung menjadi data utama yang ditampilkan
     *   di halaman frontend.
     * - FileUpload bawaan Filament berjalan melalui mekanisme Livewire temporary upload.
     *   Pada beberapa environment lokal/hosting, proses tersebut bisa terasa lambat
     *   atau berisiko tertahan saat memproses file.
     * - Dengan custom view, upload gambar diproses menggunakan form HTML native:
     *   method="POST" enctype="multipart/form-data"
     *
     * Keuntungannya:
     * - Upload gambar langsung dikirim ke controller saat tombol Save ditekan.
     * - Tidak bergantung pada temporary upload Livewire/FilePond.
     * - Proses upload lebih sederhana, stabil, dan mudah dikontrol.
     * - Nama file, lokasi penyimpanan, validasi, dan penggantian gambar bisa diatur
     *   langsung dari controller.
     * - Tampilan tetap berada di dalam panel Filament, tetapi proses upload dibuat
     *   lebih ringan dan sesuai kebutuhan project.
     *
     * Catatan:
     * Karena class ini extends Page, custom view wajib didefinisikan.
     * Jika baris $view ini dihapus atau dikomentari, halaman tidak tahu Blade mana
     * yang harus dirender sehingga form Edit Organization Structure tidak tampil.
     */

    protected string $view = 'filament.resources.organization-structures.pages.edit-organization-structure';

    public OrganizationStructure $record;

    public function mount(OrganizationStructure $record): void
    {
        $this->record = $record;
    }

    public function getTitle(): string
    {
        return 'Edit Organization Structure';
    }
}
