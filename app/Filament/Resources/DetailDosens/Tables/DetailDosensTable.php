<?php

namespace App\Filament\Resources\DetailDosens\Tables;

use App\Filament\Resources\DetailDosens\DetailDosenResource;
use App\Support\StudyProgramOptions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

                TextColumn::make('lecturer.name')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('institution')
                    ->label('Institusi')
                    ->searchable(),

                TextColumn::make('study_program')
                    ->label('Program Studi SINTA')
                    ->searchable(),

                TextColumn::make('department_names')
                    ->label('Program Studi Terhubung')
                    ->getStateUsing(fn ($record): string => self::resolveStudyProgramNames($record))
                    ->wrap(),

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
            ->actions([
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

    private static function resolveStudyProgramNames($record): string
    {
        $ids = self::postgraduateStudyProgramIds($record);

        if (empty($ids)) {
            return '-';
        }

        return collect(StudyProgramOptions::resolveNames($ids))->implode(', ');
    }

    private static function postgraduateStudyProgramIds($record): array
    {
        $sintaId = $record->sinta_id;

        if (! $sintaId) {
            return [];
        }

        if (Schema::hasTable('postgraduate_lecturers') && Schema::hasTable('postgraduate_lecturer_study_programs')) {
            $lecturerId = DB::table('postgraduate_lecturers')
                ->where('sinta_id', $sintaId)
                ->value('id');

            if ($lecturerId) {
                $ids = DB::table('postgraduate_lecturer_study_programs')
                    ->where('postgraduate_lecturer_id', $lecturerId)
                    ->pluck('study_program_id')
                    ->map(fn ($id): string => (string) $id)
                    ->toArray();

                if (! empty($ids)) {
                    return $ids;
                }
            }
        }

        if (! Schema::hasTable('departement')) {
            return [];
        }

        return DB::table('departement')
            ->where('sinta_id', $sintaId)
            ->pluck('id_departement')
            ->map(fn ($id): string => (string) $id)
            ->toArray();
    }
}
