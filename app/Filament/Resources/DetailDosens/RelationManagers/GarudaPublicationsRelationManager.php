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

class GarudaPublicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'garudaPublications';
    protected static ?string $title = 'Publikasi Garuda';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            // Mengubah 'judul' menjadi 'title'
            Textarea::make('title')->label('Judul')->required()->columnSpanFull(),
            // MENAMBAHKAN KOLOM BARU: authors & author_order
            TextInput::make('authors')->label('Penulis'),
            TextInput::make('author_order')->label('Urutan Penulis'),
            TextInput::make('publisher')->label('Publisher'),
            TextInput::make('journal')->label('Journal'),
            // Mengubah 'tahun' menjadi 'year'
            TextInput::make('year')->label('Tahun'),
            // MENAMBAHKAN KOLOM BARU: doi
            TextInput::make('doi')->label('DOI'),
            TextInput::make('accreditation')->label('Akreditasi'),
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
                // MENAMBAHKAN KOLOM BARU: authors & author_order
                TextColumn::make('authors')->label('Penulis'),
                TextColumn::make('author_order')->label('Urutan Penulis'),
                TextColumn::make('publisher')->label('Publisher'),
                TextColumn::make('journal')->label('Journal'),
                TextColumn::make('year')->label('Tahun')->sortable(),
                // MENAMBAHKAN KOLOM BARU: doi
                TextColumn::make('doi')->label('DOI'),
                TextColumn::make('accreditation')->label('Akreditasi'),
                
                // MENAMBAHKAN KOLOM BARU: article_url & journal_url dengan format link aman
                TextColumn::make('article_url')
                    ->label('URL Artikel')
                    ->url(fn ($state) => $state)
                    ->openUrlInNewTab(),
                TextColumn::make('journal_url')
                    ->label('URL Jurnal')
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