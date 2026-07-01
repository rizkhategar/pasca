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

class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'books';
    protected static ?string $title = 'Book Data';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('title')->label('Judul')->required()->columnSpanFull(),
            TextInput::make('authors')->label('Penulis'),
            TextInput::make('publisher')->label('Penerbit'),
            TextInput::make('year')->label('Tahun'),
            TextInput::make('city')->label('Kota'),
            TextInput::make('isbn')->label('ISBN'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label('Judul')->wrap()->searchable(),
                TextColumn::make('authors')->label('Penulis'),
                TextColumn::make('publisher')->label('Penerbit'),
                TextColumn::make('year')->label('Tahun'),
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
