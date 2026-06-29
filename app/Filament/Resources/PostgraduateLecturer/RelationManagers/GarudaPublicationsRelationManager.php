<?php

namespace App\Filament\Resources\PostgraduateLecturer\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GarudaPublicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'garudaPublications';
    protected static ?string $title = 'Publikasi Garuda';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('title')->label('Judul')->required()->columnSpanFull(),
            TextInput::make('authors')->label('Penulis'),
            TextInput::make('author_order')->label('Urutan Penulis'),
            TextInput::make('publisher')->label('Publisher'),
            TextInput::make('journal')->label('Journal'),
            TextInput::make('year')->label('Tahun'),
            TextInput::make('doi')->label('DOI'),
            TextInput::make('accreditation')->label('Akreditasi'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label('Judul')->wrap()->searchable(),
                TextColumn::make('authors')->label('Penulis'),
                TextColumn::make('author_order')->label('Urutan Penulis'),
                TextColumn::make('publisher')->label('Publisher'),
                TextColumn::make('journal')->label('Journal'),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('doi')->label('DOI'),
                TextColumn::make('accreditation')->label('Akreditasi'),
                TextColumn::make('article_url')->label('URL Artikel'),
                TextColumn::make('journal_url')->label('URL Jurnal'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
