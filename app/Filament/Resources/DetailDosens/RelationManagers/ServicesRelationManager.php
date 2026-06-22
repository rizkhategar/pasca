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

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';
    protected static ?string $title = 'Data Pengabdian';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            // Mengubah 'judul' menjadi 'title'
            Textarea::make('title')->label('Judul')->required()->columnSpanFull(),
            TextInput::make('leader')->label('Leader'),
            // Mengubah 'skema' menjadi 'scheme'
            TextInput::make('scheme')->label('Skema'),
            // MENAMBAHKAN KOLOM BARU: personnel
            TextInput::make('personnel')->label('Personil/Anggota'),
            // Mengubah 'tahun' menjadi 'year'
            TextInput::make('year')->label('Tahun'),
            // Mengubah 'dana' menjadi 'funding'
            TextInput::make('funding')->label('Dana'),
            TextInput::make('status')->label('Status'),
            // MENAMBAHKAN KOLOM BARU: source
            TextInput::make('source')->label('Sumber Dana'),
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
                TextColumn::make('leader')->label('Leader'),
                TextColumn::make('scheme')->label('Skema'),
                // MENAMBAHKAN KOLOM BARU: personnel
                TextColumn::make('personnel')->label('Personil/Anggota'),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('funding')->label('Dana'),
                TextColumn::make('status')->label('Status'),
                // MENAMBAHKAN KOLOM BARU: source
                TextColumn::make('source')->label('Sumber Dana'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}