<?php

namespace App\Filament\Resources\UndergraduateLecturers\Tables;

use App\Filament\Resources\UndergraduateLecturers\UndergraduateLecturerResource;
use App\Support\StudyProgramOptions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UndergraduateLecturersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sinta_id')->label('SINTA ID')->searchable()->sortable(),
                TextColumn::make('sintaLecturer.name')->label('Nama')->searchable(),
                TextColumn::make('institution')->label('Institusi')->searchable(),
                TextColumn::make('study_program')->label('Program Studi SINTA')->searchable(),
                TextColumn::make('study_program_names')
                    ->label('Program Studi Terhubung')
                    ->getStateUsing(fn ($record): string => self::resolveStudyProgramNames($record))
                    ->wrap(),
            ])
            ->actions([
                ViewAction::make()->url(fn ($record) => UndergraduateLecturerResource::getUrl('view', ['record' => $record])),
                EditAction::make()->url(fn ($record) => UndergraduateLecturerResource::getUrl('edit', ['record' => $record])),
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
        $ids = $record->studyPrograms()
            ->pluck('study_programs.id')
            ->map(fn ($id): string => (string) $id)
            ->toArray();

        if (empty($ids)) {
            return '-';
        }

        return collect(StudyProgramOptions::resolveNames($ids))->implode(', ');
    }
}
