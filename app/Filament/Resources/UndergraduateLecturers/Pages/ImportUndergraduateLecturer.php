<?php

namespace App\Filament\Resources\UndergraduateLecturers\Pages;

use App\Filament\Resources\UndergraduateLecturers\UndergraduateLecturerResource;
use App\Models\SintaLecturer;
use App\Support\StudyProgramOptions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;

class ImportUndergraduateLecturer extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = UndergraduateLecturerResource::class;

    protected string $view = 'filament.resources.detail-dosens.pages.import-detail-dosen';

    protected static ?string $title = 'Import Undergraduate Lecturer';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Target Import Undergraduate')
                    ->description('Dropdown program studi pada halaman ini menampilkan semua jenjang kecuali Magister.')
                    ->schema([
                        Select::make('sinta_id')
                            ->label('Pilih Dosen SINTA')
                            ->options($this->lecturerOptions())
                            ->getSearchResultsUsing(fn (string $search): array => $this->lecturerOptions($search))
                            ->searchable()
                            ->required(),

                        Select::make('jurusan')
                            ->label('Program Studi Non-Magister')
                            ->options(fn (): array => StudyProgramOptions::undergraduateOptions())
                            ->searchable()
                            ->multiple()
                            ->required()
                            ->native(false),
                    ]),
            ])
            ->statePath('data');
    }

    protected function lecturerOptions(?string $search = null): array
    {
        return SintaLecturer::query()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sinta_id', 'like', "%{$search}%");
            }))
            ->orderBy('name', 'asc')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (SintaLecturer $lecturer): array => [
                $lecturer->sinta_id => trim(($lecturer->name ?? '-') . ' (' . $lecturer->sinta_id . ')'),
            ])
            ->toArray();
    }
}
