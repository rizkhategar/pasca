<?php

namespace App\Filament\Resources\AboutPascasarjanas\Pages;

use App\Filament\Resources\AboutPascasarjanas\AboutPascasarjanaResource;
use App\Models\AboutPascasarjana;
use Filament\Resources\Pages\Page;

class EditAboutPascasarjana extends Page
{
    protected static string $resource = AboutPascasarjanaResource::class;

    /**
     * Halaman ini sengaja menggunakan custom Blade view, bukan default Filament form.
     *
     * Alasan:
     * - Field FileUpload bawaan Filament menggunakan mekanisme Livewire temporary upload.
     * - Pada environment lokal/hosting tertentu, proses upload Livewire dapat berhenti di status
     *   "Uploading 100%" atau "Uploading files..." meskipun file sudah berhasil dipilih.
     * - Akibatnya tombol Save/Save Changes tidak aktif kembali dan data tidak bisa disimpan.
     *
     * Dengan custom view, proses upload gambar memakai form HTML native:
     * method="POST" enctype="multipart/form-data"
     *
     * Keuntungannya:
     * - Upload tidak melewati temporary upload Livewire/FilePond.
     * - File langsung diproses oleh controller saat tombol Save Changes ditekan.
     * - Gambar lama tetap bisa dipertahankan jika tidak ada upload baru.
     * - Jika ada upload baru, gambar lama dapat diganti/ditimpa secara lebih stabil.
     * - Form tetap berada di dalam halaman Filament, tetapi proses upload dibuat lebih sederhana
     *   dan lebih aman untuk kebutuhan production/hosting.
     *
     * Catatan:
     * Jika baris $view ini dihapus/dikomentari, halaman akan kosong karena class ini extends Page,
     * bukan extends EditRecord. Pada Filament Page, custom view wajib didefinisikan agar halaman
     * mengetahui Blade mana yang harus dirender.
     */

    protected string $view = 'filament.resources.about-pascasarjanas.pages.edit-about-pascasarjana';

    public mixed $record = null;

    public function mount(AboutPascasarjana $record): void
    {
        $this->record = $record;
    }
}
