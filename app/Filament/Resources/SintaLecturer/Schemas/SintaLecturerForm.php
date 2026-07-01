<?php

namespace App\Filament\Resources\SintaLecturer\Schemas;

use App\Models\PostgraduateLecturer;
use App\Models\SintaLecturer;
use App\Models\StudyProgram;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class SintaLecturerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lecturer Detail')
                    ->description('Detail data dosen ditampilkan dari sumber tabel yang sesuai tanpa data ganda.')
                    ->schema([
                        Placeholder::make('lecturer_detail_list')
                            ->hiddenLabel()
                            ->content(fn ($record): HtmlString => self::lecturerDetailHtml($record)),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function lecturerDetailHtml($record): HtmlString
    {
        $detail = $record?->detail;
        $sintaId = self::firstFilled($record?->sinta_id, $detail?->sinta_id);

        $postgraduateLecturer = PostgraduateLecturer::query()
            ->where('sinta_id', $sintaId)
            ->first();

        $sintaLecturer = SintaLecturer::query()
            ->where('sinta_id', $sintaId)
            ->first();

        return self::detailList([
            ['Profile Photo', self::profilePhotoHtml($postgraduateLecturer?->sinta_id, $postgraduateLecturer?->profile_photo), true],
            ['SINTA ID', $postgraduateLecturer?->sinta_id],
            ['Lecturer Name', $sintaLecturer?->name],
            ['Institution', $postgraduateLecturer?->institution],
            ['Study Programs', self::postgraduateStudyPrograms($postgraduateLecturer)],
            ['SINTA Study Program', $detail?->study_program],
            ['Research Interests', $detail?->research_interests],
            ['SINTA Score Overall', self::number($detail?->sinta_score_overall)],
            ['SINTA Score 3Yr', self::number($detail?->sinta_score_3yr)],
            ['Affiliation Score', self::number($detail?->affil_score)],
            ['Affiliation Score 3Yr', self::number($detail?->affil_score_3yr)],
        ]);
    }

    private static function detailList(array $rows): HtmlString
    {
        $styles = '
            <style>
                .lecturer-detail-list {
                    --ld-border: #e5e7eb;
                    --ld-surface: #ffffff;
                    --ld-label-bg: #f8fafc;
                    --ld-label-text: #475569;
                    --ld-value-text: #0f172a;
                    --ld-muted-text: #94a3b8;
                    --ld-chip-bg: #f8fafc;
                    --ld-chip-text: #475569;
                    overflow: hidden;
                    border: 1px solid var(--ld-border);
                    border-radius: 14px;
                    background: var(--ld-surface);
                }
                .lecturer-detail-list-row {
                    display: grid;
                    grid-template-columns: minmax(180px, 260px) 1fr;
                    border-bottom: 1px solid var(--ld-border);
                }
                .lecturer-detail-list-row:last-child {
                    border-bottom: 0;
                }
                .lecturer-detail-list-label {
                    padding: 14px 16px;
                    background: var(--ld-label-bg);
                    color: var(--ld-label-text);
                    font-size: 13px;
                    font-weight: 700;
                }
                .lecturer-detail-list-value {
                    padding: 14px 16px;
                    color: var(--ld-value-text);
                    font-size: 14px;
                    font-weight: 600;
                    line-height: 1.6;
                    word-break: break-word;
                }
                .lecturer-detail-list-muted {
                    color: var(--ld-muted-text);
                }
                .lecturer-detail-list-photo {
                    width: 96px;
                    height: 128px;
                    object-fit: cover;
                    object-position: center;
                    border-radius: 12px;
                    border: 1px solid var(--ld-border);
                    background: var(--ld-label-bg);
                    box-shadow: 0 10px 22px rgba(15, 23, 42, .16);
                }
                .lecturer-detail-list-chip {
                    display: inline-flex;
                    border-radius: 999px;
                    padding: 6px 10px;
                    border: 1px solid var(--ld-border);
                    background: var(--ld-chip-bg);
                    color: var(--ld-chip-text);
                    font-size: 12px;
                    font-weight: 700;
                }
                .dark .lecturer-detail-list,
                [data-theme="dark"] .lecturer-detail-list {
                    --ld-border: #374151;
                    --ld-surface: #111827;
                    --ld-label-bg: #1f2937;
                    --ld-label-text: #d1d5db;
                    --ld-value-text: #f9fafb;
                    --ld-muted-text: #9ca3af;
                    --ld-chip-bg: #1f2937;
                    --ld-chip-text: #d1d5db;
                }
                @media (max-width: 640px) {
                    .lecturer-detail-list-row {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
        ';

        $html = $styles . '<div class="lecturer-detail-list">';

        foreach ($rows as $row) {
            $label = (string) ($row[0] ?? '');
            $value = $row[1] ?? null;
            $isHtml = (bool) ($row[2] ?? false);

            $html .= self::detailRow($label, $value, $isHtml);
        }

        $html .= '</div>';

        return new HtmlString($html);
    }

    private static function detailRow(string $label, mixed $value, bool $isHtml = false): string
    {
        $displayValue = $isHtml ? self::htmlDisplay($value) : e(self::display($value));

        return '
            <div class="lecturer-detail-list-row">
                <div class="lecturer-detail-list-label">' . e($label) . '</div>
                <div class="lecturer-detail-list-value">' . $displayValue . '</div>
            </div>
        ';
    }

    private static function htmlDisplay(mixed $value): string
    {
        return filled($value) ? (string) $value : '<span class="lecturer-detail-list-muted">-</span>';
    }

    private static function display(mixed $value): string
    {
        return filled($value) ? (string) $value : '-';
    }

    private static function number(mixed $value): string
    {
        return $value !== null && $value !== '' ? number_format((int) $value) : '-';
    }

    private static function firstFilled(mixed ...$values): mixed
    {
        foreach ($values as $value) {
            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }

    private static function profilePhotoHtml(?string $sintaId, ?string $profilePhoto = null): string
    {
        $safeSintaId = Str::of((string) $sintaId)->trim()->replaceMatches('/[^A-Za-z0-9_-]/', '')->toString();
        if (! $safeSintaId) {
            return '-';
        }

        $officialPath = "sinta-lecturers/{$safeSintaId}_PL.jpg";
        $defaultPath = "sinta-lecturers/{$safeSintaId}.jpg";

        if (filled($profilePhoto)) {
            if (filter_var($profilePhoto, FILTER_VALIDATE_URL)) {
                return self::imageHtml((string) $profilePhoto, 'Foto Profil Pascasarjana');
            }

            $normalizedPath = trim(str_replace('\\', '/', (string) $profilePhoto), '/');

            if (Storage::disk('public')->exists($normalizedPath)) {
                $caption = basename($normalizedPath) === "{$safeSintaId}_PL.jpg"
                    ? 'Foto Resmi Admin (_PL)'
                    : 'Foto Bawaan SINTA';

                return self::imageHtml(Storage::disk('public')->url($normalizedPath) . '?v=' . time(), $caption);
            }
        }

        if (Storage::disk('public')->exists($officialPath)) {
            PostgraduateLecturer::where('sinta_id', $safeSintaId)
                ->where(function ($query): void {
                    $query->whereNull('profile_photo')
                        ->orWhere('profile_photo', 'not like', '%_PL.jpg');
                })
                ->update(['profile_photo' => $officialPath]);

            return self::imageHtml(Storage::disk('public')->url($officialPath) . '?v=' . time(), 'Foto Resmi Admin (_PL)');
        }

        if (Storage::disk('public')->exists($defaultPath)) {
            PostgraduateLecturer::where('sinta_id', $safeSintaId)
                ->whereNull('profile_photo')
                ->update(['profile_photo' => $defaultPath]);

            return self::imageHtml(Storage::disk('public')->url($defaultPath) . '?v=' . time(), 'Foto Bawaan SINTA');
        }

        return '-';
    }

    private static function imageHtml(string $url, string $caption): string
    {
        $safeUrl = e($url);
        $safeCaption = e($caption);

        return "
            <div style=\"display:flex;align-items:center;gap:14px;flex-wrap:wrap;\">
                <img src=\"{$safeUrl}\" alt=\"Profile Photo\" class=\"lecturer-detail-list-photo\" />
                <span class=\"lecturer-detail-list-chip\">{$safeCaption}</span>
            </div>
        ";
    }

    private static function postgraduateStudyPrograms(?PostgraduateLecturer $postgraduateLecturer): string
    {
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
}
