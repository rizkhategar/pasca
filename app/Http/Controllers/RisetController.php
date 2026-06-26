<?php

namespace App\Http\Controllers;

use App\Models\SintaLecturerDetail;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class RisetController extends Controller
{
    public function listDosen(Request $request)
    {
        $academicProgramsNav = AcademicController::getNavigationData();

        $query = SintaLecturerDetail::with('postgraduateLecturer.studyPrograms');

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sinta_id', 'like', '%' . $request->search . '%')
                    ->orWhereHas('postgraduateLecturer', function ($sub) use ($request) {
                        $sub->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->has('jurusan') && $request->jurusan != '') {
            $query->whereHas('postgraduateLecturer.studyPrograms', function ($subQuery) use ($request) {
                $subQuery->where('study_programs.id', $request->jurusan)
                    ->orWhere('study_programs.id_unw_program_studi', $request->jurusan);
            });
        }

        $dosens = $query->paginate(10)->through(function ($dosen) {
            return $this->transformToIndonesianAttributes($dosen);
        });

        return view('research.lecturers', compact('dosens', 'academicProgramsNav'));
    }

    public function detailDosen($sinta_id)
    {
        $dosen = SintaLecturerDetail::with([
            'postgraduateLecturer.studyPrograms',
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
            'serviceYearlies',
        ])->findOrFail($sinta_id);

        $dosen = $this->transformToIndonesianAttributes($dosen);

        return view('research.detail', compact('dosen'));
    }

    private function transformToIndonesianAttributes($dosen)
    {
        if (! $dosen) {
            return $dosen;
        }

        $dosen->nama = $dosen->postgraduateLecturer->name ?? $dosen->name;
        $dosen->profile_photo = $this->resolveLecturerPhotoPath($dosen);

        $studyProgramMap = Cache::remember('study_programs_select_import', now()->addHours(12), function () {
            return StudyProgram::query()
                ->where('unw_fakultas_nama', 'Pascasarjana')
                ->orderBy('jenjang')
                ->orderBy('nama')
                ->get()
                ->mapWithKeys(fn (StudyProgram $program) => [
                    (string) $program->id => $program->display_name,
                ])
                ->toArray();
        });

        $associatedIds = $dosen->postgraduateLecturer?->studyPrograms
            ? $dosen->postgraduateLecturer->studyPrograms->pluck('id')->toArray()
            : [];

        if (! empty($associatedIds)) {
            $mappedNames = array_map(fn ($id) => $studyProgramMap[(string) $id] ?? $id, $associatedIds);
            $dosen->program_studi = implode(', ', $mappedNames);
        } else {
            $dosen->program_studi = $dosen->study_program ?? '-';
        }

        if ($dosen->relationLoaded('scopusPublications')) {
            foreach ($dosen->scopusPublications as $item) {
                $item->judul = $item->title;
                $item->tahun = $item->year;
                $item->url_artikel = $item->article_url;
                $item->url_journal = $item->journal_url;
            }
        }

        if ($dosen->relationLoaded('scholarPublications')) {
            foreach ($dosen->scholarPublications as $item) {
                $item->judul = $item->title;
                $item->tahun = $item->year;
                $item->url_scholar = $item->scholar_url;
            }
        }

        if ($dosen->relationLoaded('garudaPublications')) {
            foreach ($dosen->garudaPublications as $item) {
                $item->judul = $item->title;
                $item->tahun = $item->year;
                $item->url_artikel = $item->article_url;
                $item->url_journal = $item->journal_url;
            }
        }

        if ($dosen->relationLoaded('books')) {
            foreach ($dosen->books as $item) {
                $item->judul = $item->title;
                $item->kategori = $item->category;
                $item->penerbit = $item->publisher;
                $item->tahun = $item->year;
            }
        }

        if ($dosen->relationLoaded('researches')) {
            foreach ($dosen->researches as $item) {
                $item->judul = $item->title;
                $item->skema = $item->scheme;
                $item->tahun = $item->year;
                $item->dana = $item->funding;
                $item->personils = $item->personnel;
            }
        }

        if ($dosen->relationLoaded('services')) {
            foreach ($dosen->services as $item) {
                $item->judul = $item->title;
                $item->skema = $item->scheme;
                $item->tahun = $item->year;
                $item->dana = $item->funding;
                $item->personils = $item->personnel;
            }
        }

        if ($dosen->relationLoaded('researchYearlies')) {
            foreach ($dosen->researchYearlies as $item) {
                $item->tahun = $item->year;
                $item->jumlah = $item->count;
            }
        }

        if ($dosen->relationLoaded('serviceYearlies')) {
            foreach ($dosen->serviceYearlies as $item) {
                $item->tahun = $item->year;
                $item->jumlah = $item->count;
            }
        }

        if ($dosen->relationLoaded('scopusYearlyStats')) {
            foreach ($dosen->scopusYearlyStats as $item) {
                $item->tahun = $item->year;
                $item->jumlah = $item->count;
            }
        }

        if ($dosen->relationLoaded('garudaYearlyStats')) {
            foreach ($dosen->garudaYearlyStats as $item) {
                $item->tahun = $item->year;
                $item->jumlah = $item->articles;
            }
        }

        if ($dosen->relationLoaded('scholarYearlyStats')) {
            foreach ($dosen->scholarYearlyStats as $item) {
                $item->tahun = $item->year;
            }
        }

        return $dosen;
    }

    private function resolveLecturerPhotoPath($dosen): ?string
    {
        $safeSintaId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $dosen->sinta_id);

        if ($safeSintaId === '') {
            return null;
        }

        $customPath = "sinta-lecturers/{$safeSintaId}_PL.jpg";
        $scrapedPath = "sinta-lecturers/{$safeSintaId}.jpg";

        if (Storage::disk('public')->exists($customPath)) {
            return url('storage/' . $customPath);
        }

        if (Storage::disk('public')->exists($scrapedPath)) {
            return url('storage/' . $scrapedPath);
        }

        $storedPath = $dosen->postgraduateLecturer->profile_photo ?? $dosen->profile_photo ?? null;

        if (! $storedPath) {
            return null;
        }

        $storedPath = trim(str_replace('\\', '/', $storedPath), '/');

        if ($storedPath === '') {
            return null;
        }

        if (! str_contains($storedPath, '/')) {
            $storedPath = 'sinta-lecturers/' . $storedPath;
        }

        return Storage::disk('public')->exists($storedPath) ? url('storage/' . $storedPath) : null;
    }
}
