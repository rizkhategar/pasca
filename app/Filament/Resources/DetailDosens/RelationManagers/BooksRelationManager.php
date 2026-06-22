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

class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'books';
    protected static ?string $title = 'Data Buku';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            // Mengubah 'judul' menjadi 'title'
            Textarea::make('title')->label('Judul')->required()->columnSpanFull(),
            TextInput::make('authors')->label('Penulis'),
            // Mengubah 'penerbit' menjadi 'publisher'
            TextInput::make('publisher')->label('Penerbit'),
            // Mengubah 'tahun' menjadi 'year'
            TextInput::make('year')->label('Tahun'),
            // MENAMBAHKAN KOLOM BARU: city
            TextInput::make('city')->label('Kota'),
            TextInput::make('isbn')->label('ISBN'),
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
                TextColumn::make('authors')->label('Penulis'),
                TextColumn::make('publisher')->label('Penerbit'),
                TextColumn::make('year')->label('Tahun'),
                // MENAMBAHKAN KOLOM BARU: city
                TextColumn::make('city')->label('Kota'),
                TextColumn::make('isbn')->label('ISBN'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}