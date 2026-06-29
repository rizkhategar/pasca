<?php

namespace App\Filament\Resources\UndergraduateLecturer\Tables;

use App\Filament\Resources\UndergraduateLecturer\UndergraduateLecturerResource;
use App\Models\StudyProgram;
use App\Models\UndergraduateLecturer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class UndergraduateLecturerTable
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

                TextColumn::make('undergraduate_study_program_names')
                    ->label('Program Studi Undergraduate')
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
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn ($record) => UndergraduateLecturerResource::getUrl('view', ['record' => $record])),

                EditAction::make()
                    ->url(fn ($record) => UndergraduateLecturerResource::getUrl('edit', ['record' => $record])),

                DeleteAction::make()
                    ->label('Remove from Undergraduate')
                    ->action(function ($record): void {
                        UndergraduateLecturer::where('sinta_id', $record->sinta_id)->delete();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Remove selected from Undergraduate')
                        ->action(function ($records): void {
                            UndergraduateLecturer::whereIn('sinta_id', $records->pluck('sinta_id')->filter()->all())->delete();
                        }),
                ]),
            ]);
    }

    private static function resolveStudyProgramNames($record): string
    {
        $studyProgramIds = $record->undergraduateStudyProgramPivots()
            ->pluck('study_program_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        if ($studyProgramIds->isEmpty()) {
            return '-';
        }

        $studyProgramMap = self::getUndergraduateStudyProgramMap();

        return $studyProgramIds
            ->map(fn (string $id): string => $studyProgramMap[$id] ?? $id)
            ->implode(', ');
    }

    private static function getUndergraduateStudyProgramMap(): array
    {
        return Cache::remember('study_programs_undergraduate_select_import', now()->addHours(12), function () {
            return StudyProgram::query()
                ->where(function ($query) {
                    $query->whereNull('jenjang')
                        ->orWhere('jenjang', 'not like', '%Magister%');
                })
                ->where(function ($query) {
                    $query->whereNull('jenjang_nama_singkat')
                        ->orWhere('jenjang_nama_singkat', '!=', 'S2');
                })
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
