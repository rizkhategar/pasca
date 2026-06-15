<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrganizationStructureController;
use App\Http\Controllers\OrganizationStructureUploadController;
use App\Http\Controllers\RisetController;
use App\Http\Controllers\ScrapController;
use App\Http\Controllers\SliderUploadController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\VisiMisiController;
use App\Models\OrganizationStructure;
use App\Models\Slider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    $sliders = Slider::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->oldest('id')
        ->get();

    return view('home', compact('sliders'));
})->name('home');

Route::get('/sliders/{slider}/image', function (Slider $slider) {
    abort_unless($slider->image_path, 404);
    abort_unless(Storage::disk('public')->exists($slider->image_path), 404);

    return response()
        ->file(Storage::disk('public')->path($slider->image_path), [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
})->name('sliders.image');

Route::get('/organization-structures/{organizationStructure}/image', function (OrganizationStructure $organizationStructure) {
    abort_unless($organizationStructure->image_path, 404);
    abort_unless(Storage::disk('public')->exists($organizationStructure->image_path), 404);

    return response()
        ->file(Storage::disk('public')->path($organizationStructure->image_path), [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
})->name('organization-structures.image');

Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
Route::get('/berita/search', [NewsController::class, 'search'])->name('news.search');
Route::get('/berita/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/kontak', [ContactController::class, 'index'])->name('contact.index');
Route::get('/akademik/{slug}', [AcademicController::class, 'show'])->name('akademik.show');

Route::get('/akademik/magister-hukum', function () {
    return view('akademik');
})->name('akademik.hukum');

Route::get('/visi-misi', [VisiMisiController::class, 'index'])->name('visi-misi');
Route::get('/profil/struktur-organisasi', [OrganizationStructureController::class, 'index'])->name('profil.struktur-organisasi');
Route::get('/tentang-pascasarjana', [AboutController::class, 'index'])->name('tentang');

Route::get('/scrap/ambildatadosen', [ScrapController::class, 'index'])->name('scrap.index');
Route::get('/scrap/perbarui-dosen', [ScrapController::class, 'perbaruiDosen'])->name('scrap.perbaruiDosen');
Route::get('/scrap/ambil-detail/{sinta_id}', [ScrapController::class, 'ambilDetail'])->name('scrap.ambilDetail');
Route::get('/scrap/import/{sinta_id}', [ScrapController::class, 'importData'])->name('scrap.importData');

Route::get('/riset-dosen', [RisetController::class, 'listDosen'])->name('riset.dosen');
Route::get('/riset-dosen/detail/{sinta_id}', [RisetController::class, 'detailDosen'])->name('riset.detail');

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/organization-structures/custom-create', [OrganizationStructureUploadController::class, 'create'])->name('admin.organization-structures.create-custom');
    Route::get('/admin/organization-structures/{organizationStructure}/custom-edit', [OrganizationStructureUploadController::class, 'edit'])->name('admin.organization-structures.edit-custom');
    Route::post('/admin/organization-structures/upload', [OrganizationStructureUploadController::class, 'store'])->name('admin.organization-structures.store');
    Route::put('/admin/organization-structures/{organizationStructure}/upload', [OrganizationStructureUploadController::class, 'update'])->name('admin.organization-structures.update');

    Route::get('/admin/sliders/custom-create', [SliderUploadController::class, 'create'])->name('admin.sliders.create-custom');
    Route::get('/admin/sliders/{slider}/custom-edit', [SliderUploadController::class, 'edit'])->name('admin.sliders.edit-custom');
    Route::post('/admin/sliders/upload', [SliderUploadController::class, 'store'])->name('admin.sliders.store');
    Route::put('/admin/sliders/{slider}/upload', [SliderUploadController::class, 'update'])->name('admin.sliders.update');
    Route::post('/admin/scrap/tambah-dosen-manual', [ScrapController::class, 'tambahDosenManual'])->name('scrap.tambahDosenManual');
});
