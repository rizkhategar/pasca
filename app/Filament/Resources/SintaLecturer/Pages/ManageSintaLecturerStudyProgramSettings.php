<?php

namespace App\Filament\Resources\SintaLecturer\Pages;

use App\Filament\Resources\SintaLecturer\SintaLecturerResource;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerStudyProgramSetting;
use App\Models\StudyProgram;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManageSintaLecturerStudyProgramSettings extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = SintaLecturerResource::class;

    protected string $view = 'filament.resources.sinta-lecturer.pages.manage-sinta-lecturer-study-program-settings';

    protected static ?string $title = 'Setting Prodi Fetch All';

    protected static ?string $navigationLabel = 'Setting Prodi Fetch All';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->sintaLecturerQuery())
            ->columns([
                TextColumn::make('sinta_id')
                    ->label('SINTA ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Dosen')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('selected_study_programs')
                    ->label('Program Studi')
                    ->state(fn (SintaLecturer $record): string => $this->selectedStudyProgramLabels($record))
                    ->badge()
                    ->separator(', ')
                    ->placeholder('Belum dipilih / Null')
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('study_program_filter')
                    ->label('Filter Program Studi')
                    ->options(fn (): array => $this->studyProgramFilterOptions())
                    ->searchable()
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        $value = data_get($data, 'value');

                        if (! $value) {
                            return $query;
                        }

                        if ($value === '__selected__') {
                            return $query->whereHas('studyProgramSettings', function (Builder $settingQuery): void {
                                $settingQuery->whereNotNull('study_program_id')
                                    ->whereIn('study_program_id', StudyProgram::query()->select('id'));
                            });
                        }

                        if ($value === '__empty__') {
                            return $query->whereDoesntHave('studyProgramSettings', function (Builder $settingQuery): void {
                                $settingQuery->whereNotNull('study_program_id')
                                    ->whereIn('study_program_id', StudyProgram::query()->select('id'));
                            });
                        }

                        if (str_starts_with((string) $value, 'study_program:')) {
                            $studyProgramId = (int) str_replace('study_program:', '', (string) $value);

                            return $query->whereHas('studyProgramSettings', function (Builder $settingQuery) use ($studyProgramId): void {
                                $settingQuery->where('study_program_id', $studyProgramId);
                            });
                        }

                        return $query;
                    }),
            ])
            ->actions([
                Action::make('aturProdi')
                    ->label('Atur Prodi')
                    ->icon('heroicon-o-academic-cap')
                    ->modalHeading(fn (SintaLecturer $record): string => 'Atur Program Studi - ' . $record->name)
                    ->modalDescription('Pilih satu atau beberapa program studi untuk dosen ini. Jika dikosongkan, data akan disimpan sebagai Belum dipilih / Null.')
                    ->fillForm(fn (SintaLecturer $record): array => [
                        'study_program_ids' => $this->selectedStudyProgramIds($record),
                    ])
                    ->form([
                        Select::make('study_program_ids')
                            ->label('Program Studi')
                            ->options(fn (): array => $this->studyProgramOptions())
                            ->multiple()
                            ->searchable()
                            ->native(false)
                            ->placeholder('Belum dipilih / Null'),
                    ])
                    ->action(function (SintaLecturer $record, array $data): void {
                        $this->saveStudyProgramSettings(
                            $record,
                            data_get($data, 'study_program_ids', []),
                        );

                        Notification::make()
                            ->title('Program studi diperbarui')
                            ->body("Setting prodi untuk {$record->name} ({$record->sinta_id}) sudah disimpan.")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('name')
            ->paginated([10, 25, 50, 100]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToImport')
                ->label('Kembali ke Import')
                ->icon('heroicon-o-arrow-left')
                ->url(SintaLecturerResource::getUrl('import')),
        ];
    }

    protected function sintaLecturerQuery(): Builder
    {
        return SintaLecturer::query()
            ->select(['sinta_id', 'name'])
            ->with(['studyProgramSettings' => function ($query): void {
                $query->select(['id', 'sinta_id', 'study_program_id'])
                    ->with(['studyProgram:id,display_name'])
                    ->orderByRaw('study_program_id IS NULL')
                    ->orderBy('id');
            }]);
    }

    protected function selectedStudyProgramIds(SintaLecturer $record): array
    {
        return $record->studyProgramSettings
            ->pluck('study_program_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function selectedStudyProgramLabels(SintaLecturer $record): string
    {
        return $record->studyProgramSettings
            ->filter(fn (SintaLecturerStudyProgramSetting $setting): bool => filled($setting->study_program_id))
            ->map(fn (SintaLecturerStudyProgramSetting $setting): ?string => $setting->studyProgram?->display_name)
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');
    }

    protected function saveStudyProgramSettings(SintaLecturer $record, mixed $state): void
    {
        $studyProgramIds = collect(is_array($state) ? $state : [$state])
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();

        SintaLecturerStudyProgramSetting::query()
            ->where('sinta_id', $record->sinta_id)
            ->delete();

        if ($studyProgramIds->isEmpty()) {
            SintaLecturerStudyProgramSetting::query()->create([
                'sinta_id' => (string) $record->sinta_id,
                'study_program_id' => null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $record->unsetRelation('studyProgramSettings');

            return;
        }

        foreach ($studyProgramIds as $studyProgramId) {
            SintaLecturerStudyProgramSetting::query()->create([
                'sinta_id' => (string) $record->sinta_id,
                'study_program_id' => (int) $studyProgramId,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        }

        $record->unsetRelation('studyProgramSettings');
    }

    protected function studyProgramFilterOptions(): array
    {
        return [
            '__selected__' => 'Sudah dipilih',
            '__empty__' => 'Belum dipilih / Null',
        ] + collect($this->studyProgramOptions())
            ->mapWithKeys(fn (string $label, int|string $id): array => [
                'study_program:' . $id => $label,
            ])
            ->toArray();
    }

    protected function studyProgramOptions(): array
    {
        return StudyProgram::query()
            ->orderBy('jenjang')
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn (StudyProgram $program): array => [
                $program->id => $program->display_name,
            ])
            ->toArray();
    }
}
