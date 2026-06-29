<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AcademicController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrganizationStructureController;
use App\Http\Controllers\RisetController;
use App\Http\Controllers\ScrapController;
use App\Http\Controllers\VisionMissionController;
use App\Models\AboutPostgraduate;
use App\Models\OrganizationalStructure;
use App\Models\Slider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/storage/{path}', function (string $path) {
    $path = normalizePublicStoragePath($path);
    abort_if(Str::contains((string) $path, ['..', '\\']), 404);

    if (preg_match('#^about-pascasarjanas/(\d+)/director-image#', (string) $path, $matches)) {
        $record = AboutPostgraduate::findOrFail($matches[1]);
        $imagePath = AboutPostgraduate::normalizeImagePath($record->direktur_image);

        abort_unless($imagePath && Storage::disk('public')->exists($imagePath), 404);

        return response()->file(Storage::disk('public')->path($imagePath), [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    if (preg_match('#^about-pascasarjanas/(\d+)/point-icons/(\d+)#', (string) $path, $matches)) {
        $record = AboutPostgraduate::findOrFail($matches[1]);
        $imagePath = AboutPostgraduate::normalizeImagePath(data_get($record->points ?? [], $matches[2] . '.icon'));

        abort_unless($imagePath && Storage::disk('public')->exists($imagePath), 404);

        return response()->file(Storage::disk('public')->path($imagePath), [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    $filePath = Slider::resolveImageFilePath($path);
    abort_unless($filePath, 404);
    return response()->file($filePath, ['Cache-Control' => 'public, max-age=31536000']);
})->where('path', '.*')->name('public-storage.file');

Route::get('/sliders/{slider}/image', function (Slider $slider) {
    $filePath = $slider->resolved_image_file_path;

    abort_unless($filePath, 404);

    return response()->file($filePath, [
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ]);
})->name('sliders.image');

Route::get('/organization-structures/{organizationStructure}/image', function (OrganizationalStructure $organizationStructure) {
    $path = normalizePublicStoragePath($organizationStructure->image_path);

    abort_unless($path && Storage::disk('public')->exists($path), 404);

    return response()->file(Storage::disk('public')->path($path), ['Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0', 'Pragma' => 'no-cache', 'Expires' => '0']);
})->name('organization-structures.image');

Route::get('/about-pascasarjanas/{aboutPascasarjana}/director-image', function (AboutPostgraduate $aboutPascasarjana) {
    $path = normalizePublicStoragePath($aboutPascasarjana->direktur_image);
    abort_unless($path && Storage::disk('public')->exists($path), 404);
    return response()->file(Storage::disk('public')->path($path), ['Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0', 'Pragma' => 'no-cache', 'Expires' => '0']);
})->name('about-pascasarjanas.director-image');

Route::get('/about-pascasarjanas/{aboutPascasarjana}/point-icons/{index}', function (AboutPostgraduate $aboutPascasarjana, int $index) {
    $path = normalizePublicStoragePath(data_get($aboutPascasarjana->points ?? [], $index . '.icon'));
    abort_unless($path && Storage::disk('public')->exists($path), 404);
    return response()->file(Storage::disk('public')->path($path), ['Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0', 'Pragma' => 'no-cache', 'Expires' => '0']);
})->name('about-pascasarjanas.point-icon');

Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
Route::get('/berita/search', [NewsController::class, 'search'])->name('news.search');
Route::get('/berita/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/kontak', [ContactController::class, 'index'])->name('contact.index');
Route::get('/akademik/magister-hukum', [AcademicController::class, 'show'])->defaults('slug', 'magister-hukum')->name('akademik.hukum');
Route::get('/akademik/{slug}', [AcademicController::class, 'show'])->name('akademik.show');
Route::get('/visi-misi', [VisionMissionController::class, 'index'])->name('visi-misi');
Route::get('/profil/struktur-organisasi', [OrganizationStructureController::class, 'index'])->name('profil.struktur-organisasi');
Route::get('/tentang-pascasarjana', [AboutController::class, 'index'])->name('tentang');
Route::get('/scrap/ambildatadosen', [ScrapController::class, 'index'])->name('scrap.index');
Route::get('/scrap/perbarui-dosen', [ScrapController::class, 'perbaruiDosen'])->name('scrap.perbaruiDosen');
Route::get('/scrap/ambil-detail/{sinta_id}', [ScrapController::class, 'ambilDetail'])->name('scrap.ambilDetail');
Route::get('/scrap/import/{sinta_id}', [ScrapController::class, 'importData'])->name('scrap.importData');
Route::get('/riset-dosen', [RisetController::class, 'listDosen'])->name('riset.dosen');
Route::get('/riset-dosen/detail/{sinta_id}', [RisetController::class, 'detailDosen'])->name('riset.detail');

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/admin/scrap/tambah-dosen-manual', [ScrapController::class, 'tambahDosenManual'])->name('scrap.tambahDosenManual');
});

if (! function_exists('normalizePublicStoragePath')) {
    function normalizePublicStoragePath(mixed $path): ?string
    {
        return Slider::normalizeImagePath($path);
    }
}
