<?php

namespace App\Filament\Resources\PostgraduateLecturer\Tables;

use App\Filament\Resources\PostgraduateLecturer\PostgraduateLecturerResource;
use App\Models\PostgraduateLecturer as Lecturer;
use App\Models\StudyProgram;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class PostgraduateLecturerTable
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
                    ->label('Name')
                    ->searchable(),

                TextColumn::make('institution')
                    ->label('Institution')
                    ->searchable(),

                TextColumn::make('study_program')
                    ->label('SINTA Study Program')
                    ->searchable(),

                TextColumn::make('study_program_names')
                    ->label('Lecturer Study Programs')
                    ->getStateUsing(fn ($record): string => self::resolveStudyProgramNames($record))
                    ->wrap(),

                TextColumn::make('research_interests')
                    ->label('Research Interests')
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
                    ->url(fn ($record) => PostgraduateLecturerResource::getUrl('view', ['record' => $record])),

                EditAction::make()
                    ->url(fn ($record) => PostgraduateLecturerResource::getUrl('edit', ['record' => $record])),

                DeleteAction::make()
                    ->label('Remove from Lecturers')
                    ->action(function ($record): void {
                        Lecturer::where('sinta_id', $record->sinta_id)->delete();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Remove selected from Lecturers')
                        ->action(function ($records): void {
                            Lecturer::whereIn('sinta_id', $records->pluck('sinta_id')->filter()->all())->delete();
                        }),
                ]),
            ]);
    }

    private static function resolveStudyProgramNames($record): string
    {
        $studyProgramIds = $record->studyProgramPivots()
            ->pluck('study_program_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        if ($studyProgramIds->isEmpty()) {
            return '-';
        }

        $studyProgramMap = self::getLecturerStudyProgramMap();

        return $studyProgramIds
            ->map(fn (string $id): string => $studyProgramMap[$id] ?? $id)
            ->implode(', ');
    }

    private static function getLecturerStudyProgramMap(): array
    {
        return Cache::remember('study_programs_select_import', now()->addHours(12), function () {
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
    }
}
