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

class ScholarPublicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'scholarPublications';
    protected static ?string $title = 'Google Scholar Publications';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('title')->label('Judul')->required()->columnSpanFull(),
            TextInput::make('authors')->label('Penulis'),
            TextInput::make('source')->label('Sumber'),
            TextInput::make('year')->label('Tahun'),
            TextInput::make('citation')->label('Sitasi')->numeric(),
            TextInput::make('scholar_url')->label('URL Scholar'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label('Judul')->wrap()->searchable(),
                TextColumn::make('authors')->label('Penulis'),
                TextColumn::make('source')->label('Sumber'),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('citation')->label('Sitasi')->sortable(),
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
