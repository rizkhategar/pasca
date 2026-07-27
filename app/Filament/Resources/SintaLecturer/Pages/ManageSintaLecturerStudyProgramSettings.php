<?php

namespace App\Filament\Resources\SintaLecturer\Pages;

use App\Filament\Resources\SintaLecturer\SintaLecturerResource;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerStudyProgramSetting;
use App\Models\StudyProgram;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\SelectColumn;
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

                SelectColumn::make('study_program_id')
                    ->label('Program Studi')
                    ->options(fn (): array => $this->studyProgramOptions())
                    ->placeholder('Belum dipilih / Null')
                    ->getStateUsing(fn (SintaLecturer $record): ?int => $this->selectedStudyProgramId($record))
                    ->updateStateUsing(function (SintaLecturer $record, mixed $state): void {
                        $this->saveStudyProgramSetting($record, $state);

                        Notification::make()
                            ->title('Program studi diperbarui')
                            ->body("Setting prodi untuk {$record->name} ({$record->sinta_id}) sudah disimpan.")
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                SelectFilter::make('setting_status')
                    ->label('Status Setting')
                    ->options([
                        'selected' => 'Sudah dipilih',
                        'empty' => 'Belum dipilih / Null',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = data_get($data, 'value');

                        return match ($value) {
                            'selected' => $query->whereHas('studyProgramSettings', function (Builder $settingQuery): void {
                                $settingQuery->whereNotNull('study_program_id')
                                    ->whereIn('study_program_id', StudyProgram::query()->select('id'));
                            }),
                            'empty' => $query->whereDoesntHave('studyProgramSettings', function (Builder $settingQuery): void {
                                $settingQuery->whereNotNull('study_program_id')
                                    ->whereIn('study_program_id', StudyProgram::query()->select('id'));
                            }),
                            default => $query,
                        };
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
                    ->orderByRaw('study_program_id IS NULL')
                    ->orderBy('id');
            }]);
    }

    protected function selectedStudyProgramId(SintaLecturer $record): ?int
    {
        $studyProgramId = $record->studyProgramSettings
            ->first(fn (SintaLecturerStudyProgramSetting $setting): bool => filled($setting->study_program_id))
            ?->study_program_id;

        return $studyProgramId ? (int) $studyProgramId : null;
    }

    protected function saveStudyProgramSetting(SintaLecturer $record, mixed $state): void
    {
        $studyProgramId = filled($state) ? (int) $state : null;

        SintaLecturerStudyProgramSetting::query()
            ->where('sinta_id', $record->sinta_id)
            ->delete();

        SintaLecturerStudyProgramSetting::query()->create([
            'sinta_id' => (string) $record->sinta_id,
            'study_program_id' => $studyProgramId,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $record->unsetRelation('studyProgramSettings');
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
