<?php

namespace App\Filament\Resources\DetailDosens\RelationManagers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class ScholarPublicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'scholarPublications';
    protected static ?string $title = 'Publikasi Google Scholar';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            // Mengubah 'judul' menjadi 'title'
            Textarea::make('title')->label('Judul')->required()->columnSpanFull(),
            // MENAMBAHKAN KOLOM BARU: authors
            TextInput::make('authors')->label('Penulis'),
            TextInput::make('source')->label('Sumber'),
            // Mengubah 'tahun' menjadi 'year'
            TextInput::make('year')->label('Tahun'),
            TextInput::make('citation')->label('Sitasi')->numeric(),
            // MENAMBAHKAN KOLOM BARU: scholar_url
            TextInput::make('scholar_url')->label('URL Scholar'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // Mengubah record title attribute menjadi 'title'
            ->recordTitleAttribute('title')
            ->columns([
                // Penyesuaian nama kolom ke Bahasa Inggris dengan label tetap Bahasa Indonesia
                TextColumn::make('title')->label('Judul')->wrap()->searchable(),
                // MENAMBAHKAN KOLOM BARU: authors
                TextColumn::make('authors')->label('Penulis'),
                TextColumn::make('source')->label('Sumber'),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('citation')->label('Sitasi')->sortable(),
                
                // MENAMBAHKAN KOLOM BARU: scholar_url dengan format link aman
                TextColumn::make('scholar_url')
                    ->label('URL Scholar')
                    ->url(fn ($state) => $state)
                    ->openUrlInNewTab(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}