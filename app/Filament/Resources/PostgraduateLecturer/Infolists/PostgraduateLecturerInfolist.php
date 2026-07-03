<?php

namespace App\Filament\Resources\PostgraduateLecturer\Infolists;

use App\Models\PostgraduateLecturer;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerDetail;
use App\Models\StudyProgram;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class PostgraduateLecturerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lecturer Detail')
                    ->description('Detail data dosen ditampilkan dari postgraduate_lecturers, sinta_lecturers, dan sinta_lecturer_details sesuai sumber datanya.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('profile_photo_label')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn (): string => 'Profile Photo')
                                    ->formatStateUsing(fn (): HtmlString => self::labelOnly('Profile Photo'))
                                    ->html()
                                    ->columnSpan(2),

                                ImageEntry::make('postgraduate_profile_photo')
                                    ->hiddenLabel()
                                    ->disk('public')
                                    ->getStateUsing(fn ($record): ?string => self::profilePhotoPath($record))
                                    ->columnSpan(2),

                                TextEntry::make('postgraduate_sinta_id')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn ($record): string => self::display(self::postgraduateLecturer($record)?->sinta_id))
                                    ->formatStateUsing(fn (string $state): HtmlString => self::labelValue('SINTA ID', $state))
                                    ->html(),

                                TextEntry::make('sinta_lecturer_name')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn ($record): string => self::display(self::sintaLecturer($record)?->name))
                                    ->formatStateUsing(fn (string $state): HtmlString => self::labelValue('Lecturer Name', $state))
                                    ->html(),

                                TextEntry::make('postgraduate_institution')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn ($record): string => self::display(self::postgraduateLecturer($record)?->institution))
                                    ->formatStateUsing(fn (string $state): HtmlString => self::labelValue('Institution', $state))
                                    ->html(),

                                TextEntry::make('postgraduate_study_programs')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn ($record): string => self::studyPrograms($record))
                                    ->formatStateUsing(fn (string $state): HtmlString => self::labelValue('Postgraduate Study Programs', $state))
                                    ->html(),

                                TextEntry::make('detail_study_program')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn ($record): string => self::display(self::sintaDetail($record)?->study_program))
                                    ->formatStateUsing(fn (string $state): HtmlString => self::labelValue('Study Program', $state))
                                    ->html(),

                                TextEntry::make('detail_sinta_score_overall')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn ($record): string => self::number(self::sintaDetail($record)?->sinta_score_overall))
                                    ->formatStateUsing(fn (string $state): HtmlString => self::labelValue('SINTA Score Overall', $state))
                                    ->html(),

                                TextEntry::make('detail_sinta_score_3yr')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn ($record): string => self::number(self::sintaDetail($record)?->sinta_score_3yr))
                                    ->formatStateUsing(fn (string $state): HtmlString => self::labelValue('SINTA Score 3Yr', $state))
                                    ->html(),

                                TextEntry::make('detail_affil_score')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn ($record): string => self::number(self::sintaDetail($record)?->affil_score))
                                    ->formatStateUsing(fn (string $state): HtmlString => self::labelValue('Affiliation Score', $state))
                                    ->html(),

                                TextEntry::make('detail_affil_score_3yr')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn ($record): string => self::number(self::sintaDetail($record)?->affil_score_3yr))
                                    ->formatStateUsing(fn (string $state): HtmlString => self::labelValue('Affiliation Score 3Yr', $state))
                                    ->html(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function postgraduateLecturer($record): ?PostgraduateLecturer
    {
        $sintaId = self::sintaId($record);

        if (! filled($sintaId)) {
            return null;
        }

        return PostgraduateLecturer::query()
            ->where('sinta_id', $sintaId)
            ->first();
    }

    private static function sintaLecturer($record): ?SintaLecturer
    {
        $sintaId = self::sintaId($record);

        if (! filled($sintaId)) {
            return null;
        }

        return SintaLecturer::query()
            ->where('sinta_id', $sintaId)
            ->first();
    }

    private static function sintaDetail($record): ?SintaLecturerDetail
    {
        if ($record instanceof SintaLecturerDetail) {
            return $record;
        }

        $detail = $record?->detail ?? null;

        if ($detail instanceof SintaLecturerDetail) {
            return $detail;
        }

        $sintaId = self::sintaId($record);

        if (! filled($sintaId)) {
            return null;
        }

        return SintaLecturerDetail::query()
            ->where('sinta_id', $sintaId)
            ->first();
    }

    private static function sintaId($record): ?string
    {
        return filled($record?->sinta_id) ? (string) $record->sinta_id : null;
    }

    private static function studyPrograms($record): string
    {
        $postgraduateLecturer = self::postgraduateLecturer($record);

        if (! $postgraduateLecturer?->exists) {
            return '-';
        }

        $studyPrograms = $postgraduateLecturer->studyPrograms()
            ->orderBy('jenjang')
            ->orderBy('nama')
            ->get()
            ->map(fn (StudyProgram $program): string => $program->display_name)
            ->filter()
            ->unique()
            ->values();

        if ($studyPrograms->isEmpty()) {
            return '-';
        }

        return $studyPrograms->implode(', ');
    }

    private static function profilePhotoPath($record): ?string
    {
        $postgraduateLecturer = self::postgraduateLecturer($record);
        $sintaId = $postgraduateLecturer?->sinta_id ?? self::sintaId($record);
        $safeSintaId = Str::of((string) $sintaId)->trim()->replaceMatches('/[^A-Za-z0-9_-]/', '')->toString();

        if (! $safeSintaId) {
            return null;
        }

        if (filled($postgraduateLecturer?->profile_photo)) {
            $profilePhoto = (string) $postgraduateLecturer->profile_photo;

            if (! filter_var($profilePhoto, FILTER_VALIDATE_URL)) {
                $normalizedPath = trim(str_replace('\\', '/', $profilePhoto), '/');

                if (Storage::disk('public')->exists($normalizedPath)) {
                    return $normalizedPath;
                }
            }
        }

        $officialPath = "sinta-lecturers/{$safeSintaId}_PL.jpg";

        if (Storage::disk('public')->exists($officialPath)) {
            return $officialPath;
        }

        $defaultPath = "sinta-lecturers/{$safeSintaId}.jpg";

        if (Storage::disk('public')->exists($defaultPath)) {
            return $defaultPath;
        }

        return null;
    }

    private static function labelOnly(string $label): HtmlString
    {
        return new HtmlString('<div style="color:#f59e0b;font-weight:700;font-size:13px;line-height:1.4;">' . e($label) . '</div>');
    }

    private static function labelValue(string $label, string $value): HtmlString
    {
        return new HtmlString('
            <div style="display:flex;flex-direction:column;gap:4px;line-height:1.45;">
                <span style="color:#f59e0b;font-weight:700;font-size:13px;">' . e($label) . '</span>
                <span style="color:#ffffff;font-weight:700;font-size:14px;">' . e($value) . '</span>
            </div>
        ');
    }

    private static function display(mixed $value): string
    {
        return filled($value) ? (string) $value : '-';
    }

    private static function number(mixed $value): string
    {
        return $value !== null && $value !== '' ? number_format((int) $value) : '-';
    }
}
