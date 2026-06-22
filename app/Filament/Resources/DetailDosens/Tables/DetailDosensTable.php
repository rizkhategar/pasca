<?php

namespace App\Filament\Resources\DetailDosens\Tables;

// Import Resource utama agar bisa membaca rute URL panel
use App\Filament\Resources\DetailDosens\DetailDosenResource; 

use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DetailDosensTable
{
    public static function configure(Table $table): Table
    {
         return $table
            ->columns([
                TextColumn::make('sinta_id')
                    ->label('SINTA ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('institution')
                    ->label('Institusi')
                    ->searchable(),

                TextColumn::make('study_program')
                    ->label('Program Studi')
                    ->searchable(),

                // PERBAIKAN: Mengonversi ID (misal: "21, 22") menjadi "Jenjang + Nama" secara real-time
                TextColumn::make('department')
                    ->label('Jurusan/Departemen')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) return '-';

                        // Mengambil map data program studi dari cache yang sama
                        $jurusans = Cache::remember('academic_programs_select_import', now()->addHours(12), function () {
                            $response = Http::withoutVerifying()->get('https://panel-web.unw.ac.id/api/unw-program-studi');
                            if (!$response->successful()) return [];

                            return collect($response->json('data', []))
                                ->filter(fn($item) => isset($item['id'], $item['nama'], $item['unwFakultas']['nama']) && trim($item['unwFakultas']['nama']) === 'Pascasarjana')
                                ->mapWithKeys(fn($item) => [
                                    $item['id'] => trim(($item['jenjang'] ?? '') . ' ' . ($item['nama'] ?? ''))
                                ])
                                ->sortBy(fn($value) => $value)
                                ->toArray();
                        });

                        // Pecah string berkarakter koma menjadi array ID (contoh: "21, 22" -> [21, 22])
                        $ids = array_map('trim', explode(',', $state));
                        
                        // Map setiap ID ke label "Jenjang + Nama" yang sesuai
                        $mappedNames = array_map(fn($id) => $jurusans[$id] ?? $id, $ids);

                        // Gabungkan kembali menjadi untaian teks terbaca koma
                        return implode(', ', $mappedNames);
                    }),

                TextColumn::make('research_interests')
                    ->label('Bidang Minat')
                    ->searchable(),

                TextColumn::make('sinta_score_overall')
                    ->label('SINTA Score Overall')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('sinta_score_3yr')
                    ->label('SINTA Score 3Yr')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn ($record) => DetailDosenResource::getUrl('view', ['record' => $record])),
                
                EditAction::make()
                    ->url(fn ($record) => DetailDosenResource::getUrl('edit', ['record' => $record])),
                
                DeleteAction::make(), 
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                // PERBAIKAN: Paksa mengarah ke rute halaman penuh via getUrl()
                ViewAction::make()
                    ->url(fn ($record) => DetailDosenResource::getUrl('view', ['record' => $record])),
                
                EditAction::make()
                    ->url(fn ($record) => DetailDosenResource::getUrl('edit', ['record' => $record])),
                
                DeleteAction::make(), 
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}