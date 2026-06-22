<?php

namespace App\Filament\Resources\DetailDosens\Pages;

use App\Filament\Resources\DetailDosens\DetailDosenResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Illuminate\Database\Eloquent\Model;

class EditDetailDosen extends EditRecord
{
    protected static string $resource = DetailDosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * BRAINSTORMING OK: Intersepsi Pemuatan Data (Hydration Hook)
     * Mengisi kotak form menggunakan data PascaLecturer jika ada.
     * Jika tidak ada, pinjam data dari sinta_lecturer_details sebagai draf awal.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cari apakah dosen ini sudah terdaftar di data kustom utama
        $pasca = \App\Models\PascaLecturer::find($this->record->sinta_id);

        if ($pasca) {
            $data['name']          = $pasca->name;
            $data['institution']   = $pasca->institution;
            $data['study_program'] = $pasca->study_program;
        } else {
            // Fallback awal: pinjam data dari hasil scraping agar admin tidak mengetik dari nol
            $data['name']          = $this->record->name;
            $data['institution']   = $this->record->institution;
            $data['study_program'] = $this->record->study_program;
        }

        return $data;
    }

    /**
     * BRAINSTORMING OK: Intersepsi Penyimpanan Data (Saving Hook)
     * Mengunci tabel sinta_lecturer_details agar TIDAK BERUBAH,
     * dan mengalihkan seluruh pembaruan formulir langsung ke tabel pasca_lecturers.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Simpan atau perbarui data murni ke tabel pasca_lecturers
        \App\Models\PascaLecturer::updateOrCreate(
            ['sinta_id' => $record->sinta_id], // Kunci pencarian berdasarkan SINTA ID
            [
                'name'          => $data['name'] ?? null,
                'institution'   => $data['institution'] ?? null,
                'study_program' => $data['study_program'] ?? null,
            ]
        );

        // Catatan: Sinkronisasi tabel pivot 'departement' otomatis berjalan di latar belakang
        // karena di picu oleh 'saveRelationshipsUsing' yang sudah kita pasang di DetailDosenForm.

        // Kembalikan record lama hasil scraping seutuhnya tanpa memodifikasi kolomnya
        return $record;
    }

    /**
     * Menghilangkan semua tab relasi khusus di halaman Edit sesuai draf awal
     */
    public function getRelationManagers(): array
    {
        return [];
    }
}