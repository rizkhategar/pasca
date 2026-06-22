<?php

namespace App\Http\Controllers;

// Menggunakan model scraping sebagai query dasar agar relasi riset tetap terikat utuh
use App\Models\SintaLecturerDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class RisetController extends Controller
{
    public function listDosen(Request $request)
    {
        // Ambil data navigasi untuk filter jurusan
        $academicProgramsNav = \App\Http\Controllers\AcademicController::getNavigationData();

        // Query data dosen dengan mengikutsertakan relasi PascaLecturer (Eager Loading)
        $query = SintaLecturerDetail::with('pascaLecturer');

        // Logika Pencarian Nama / SINTA ID Lintas Tabel (SINTA & Pasca)
        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sinta_id', 'like', '%' . $request->search . '%')
                    ->orWhereHas('pascaLecturer', function ($sub) use ($request) {
                        $sub->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Logika Filter Jurusan Berdasarkan Tabel Pivot Baru 'departement'
        if ($request->has('jurusan') && $request->jurusan != '') {
            $query->whereIn('sinta_id', function ($subQuery) use ($request) {
                $subQuery->select('sinta_id')
                    ->from('departement')
                    ->where('id_departement', $request->jurusan);
            });
        }

        // Pagination data dosen (Tepat 10 data per lembar halaman)
        $dosens = $query->paginate(10)->through(function ($dosen) {
            return $this->transformToIndonesianAttributes($dosen);
        });

        return view('riset&pdm.listrisetdosen', compact('dosens', 'academicProgramsNav'));
    }

    public function detailDosen($sinta_id)
    {
        $dosen = SintaLecturerDetail::with([
            'pascaLecturer', // Ikut sertakan data kustom admin
            'scopusPublications',
            'scopusYearlyStats',
            'scholarPublications',
            'scholarYearlyStats',
            'garudaPublications',
            'garudaYearlyStats',
            'books',
            'researches',
            'researchYearlies',
            'services',
            'serviceYearlies'
        ])->findOrFail($sinta_id);

        $dosen = $this->transformToIndonesianAttributes($dosen);

        return view('riset&pdm.detailriset', compact('dosen'));
    }

    /**
     * Helper Jembatan Kompatibilitas Data & Mekanisme Fallback (Scrap vs Pasca)
     */
    private function transformToIndonesianAttributes($dosen)
    {
        if (!$dosen) return $dosen;

        // 1. MEKANISME FALLBACK PROFIL: Jika ada data PascaLecturer, pakai data tersebut. Jika tidak, pakai data SINTA.
        $dosen->nama = $dosen->pascaLecturer->name ?? $dosen->name;
        
        // 2. TRANSLASI ID PIVOT JURUSAN KE TEKS DISPLAY
        $jurusans = Cache::remember('academic_programs_select_import', now()->addHours(12), function () {
            $response = Http::withoutVerifying()->get('https://panel-web.unw.ac.id/api/unw-program-studi');
            if (!$response->successful()) return [];
            return collect($response->json('data', []))
                ->filter(fn($item) => isset($item['id'], $item['nama'], $item['unwFakultas']['nama']) && trim($item['unwFakultas']['nama']) === 'Pascasarjana')
                ->mapWithKeys(fn($item) => [
                    $item['id'] => trim(($item['jenjang'] ?? '') . ' ' . ($item['nama'] ?? ''))
                ])->toArray();
        });

        $associatedIds = DB::table('departement')
            ->where('sinta_id', $dosen->sinta_id)
            ->pluck('id_departement')
            ->toArray();

        if (!empty($associatedIds)) {
            $mappedNames = array_map(fn($id) => $jurusans[$id] ?? $id, $associatedIds);
            $dosen->program_studi = implode(', ', $mappedNames);
        } else {
            // Jika pivot kosong, fallback ke teks prodi bawaan
            $dosen->program_studi = $dosen->pascaLecturer->study_program ?? $dosen->study_program ?? '-';
        }

        // --- Sinkronisasi Variabel Bahasa Indonesia Untuk Relasi Riset ---
        if ($dosen->relationLoaded('scopusPublications')) {
            foreach ($dosen->scopusPublications as $item) { $item->judul = $item->title; $item->tahun = $item->year; }
        }
        if ($dosen->relationLoaded('scholarPublications')) {
            foreach ($dosen->scholarPublications as $item) { $item->judul = $item->title; $item->tahun = $item->year; }
        }
        if ($dosen->relationLoaded('garudaPublications')) {
            foreach ($dosen->garudaPublications as $item) { $item->judul = $item->title; $item->tahun = $item->year; }
        }
        if ($dosen->relationLoaded('books')) {
            foreach ($dosen->books as $item) { $item->judul = $item->title; $item->kategori = $item->category; $item->penerbit = $item->publisher; $item->tahun = $item->year; }
        }
        if ($dosen->relationLoaded('researches')) {
            foreach ($dosen->researches as $item) { $item->judul = $item->title; $item->skema = $item->scheme; $item->tahun = $item->year; $item->dana = $item->funding; }
        }
        if ($dosen->relationLoaded('services')) {
            foreach ($dosen->services as $item) { $item->judul = $item->title; $item->skema = $item->scheme; $item->tahun = $item->year; $item->dana = $item->funding; }
        }
        if ($dosen->relationLoaded('researchYearlies')) {
            foreach ($dosen->researchYearlies as $item) { $item->tahun = $item->year; $item->jumlah = $item->count; }
        }
        if ($dosen->relationLoaded('serviceYearlies')) {
            foreach ($dosen->serviceYearlies as $item) { $item->tahun = $item->year; $item->jumlah = $item->count; }
        }
        if ($dosen->relationLoaded('scopusYearlyStats')) {
            foreach ($dosen->scopusYearlyStats as $item) { $item->tahun = $item->year; $item->jumlah = $item->count; }
        }
        if ($dosen->relationLoaded('garudaYearlyStats')) {
            foreach ($dosen->garudaYearlyStats as $item) { $item->tahun = $item->year; }
        }
        if ($dosen->relationLoaded('scholarYearlyStats')) {
            foreach ($dosen->scholarYearlyStats as $item) { $item->tahun = $item->year; }
        }

        return $dosen;
    }
}