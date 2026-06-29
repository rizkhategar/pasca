<?php

namespace App\Filament\Resources\UndergraduateLecturers\Schemas;

use App\Support\StudyProgramOptions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UndergraduateLecturerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sinta_id')->label('SINTA ID')->required()->disabled(fn ($context) => $context === 'edit'),

                Placeholder::make('lecturer_name')
                    ->label('Nama Lengkap')
                    ->content(fn ($record) => $record?->sintaLecturer?->name ?? $record?->name ?? '-'),

                TextInput::make('institution')->label('Institusi')->default(null),
                TextInput::make('study_program')->label('Program Studi')->default(null),

                Select::make('department')
                    ->label('Program Studi Non-Magister')
                    ->options(fn (): array => StudyProgramOptions::undergraduateOptions())
                    ->searchable()
                    ->multiple()
                    ->afterStateHydrated(function (Select $component, $record) {
                        if (! $record) {
                            return;
                        }

                        $component->state(
                            $record
                                ->studyPrograms()
                                ->pluck('study_programs.id')
                                ->map(fn ($id): string => (string) $id)
                                ->toArray()
                        );
                    })
                    ->saveRelationshipsUsing(function ($record, $state) {
                        if (! $record) {
                            return;
                        }

                        $ids = collect($state ?? [])->map(fn ($id): string => trim((string) $id))->filter()->unique()->values()->toArray();
                        StudyProgramOptions::ensureStudyPrograms($ids);
                        $record->studyPrograms()->sync($ids);
                    })
                    ->dehydrated(false)
                    ->default(null),
            ]);
    }
}
