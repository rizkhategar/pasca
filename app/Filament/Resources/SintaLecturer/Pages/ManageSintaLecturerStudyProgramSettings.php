<?php

namespace App\Filament\Resources\SintaLecturer\Pages;

use App\Filament\Resources\SintaLecturer\SintaLecturerResource;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerFetchBatchItem;
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
            ->query($this->settingQueryForLatestBatch())
            ->columns([
                TextColumn::make('sinta_id')
                    ->label('SINTA ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sintaLecturer.name')
                    ->label('Nama Dosen')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('sintaLecturer.department')
                    ->label('Department SINTA')
                    ->searchable()
                    ->wrap()
                    ->placeholder('Unknown / kosong'),

                SelectColumn::make('study_program_id')
                    ->label('Program Studi')
                    ->options(fn (): array => $this->studyProgramOptions())
                    ->placeholder('Belum dipilih / Null')
                    ->afterStateUpdated(function (SintaLecturerStudyProgramSetting $record): void {
                        $record->forceFill([
                            'updated_by' => auth()->id(),
                        ])->save();

                        Notification::make()
                            ->title('Program studi diperbarui')
                            ->body("Setting prodi untuk SINTA ID {$record->sinta_id} sudah disimpan.")
                            ->success()
                            ->send();
                    }),

                TextColumn::make('studyProgram.display_name')
                    ->label('Prodi Tersimpan')
                    ->placeholder('Belum dipilih / Null')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
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
                            'selected' => $query->whereNotNull('study_program_id')
                                ->whereIn('study_program_id', StudyProgram::query()->select('id')),
                            'empty' => $query->where(function (Builder $subQuery): void {
                                $subQuery->whereNull('study_program_id')
                                    ->orWhereNotIn('study_program_id', StudyProgram::query()->select('id'));
                            }),
                            default => $query,
                        };
                    }),
            ])
            ->defaultSort('sinta_id')
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

    protected function settingQueryForLatestBatch(): Builder
    {
        $latestBatchId = SintaLecturerFetchBatch::query()->latest('id')->value('id');

        return SintaLecturerStudyProgramSetting::query()
            ->with(['sintaLecturer', 'studyProgram'])
            ->when($latestBatchId, function (Builder $query) use ($latestBatchId): void {
                $query->whereIn('sinta_id', SintaLecturerFetchBatchItem::query()
                    ->where('batch_id', $latestBatchId)
                    ->whereIn('status', ['success', 'success_with_warning'])
                    ->select('sinta_id'));
            })
            ->when(! $latestBatchId, fn (Builder $query): Builder => $query->whereRaw('1 = 0'));
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
