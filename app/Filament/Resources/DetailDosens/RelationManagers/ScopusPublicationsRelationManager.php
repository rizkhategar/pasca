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

class ScopusPublicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'scopusPublications';
    protected static ?string $title = 'Publikasi Scopus';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('title')->required()->columnSpanFull(),
            TextInput::make('journal'),
            TextInput::make('year'),
            TextInput::make('citation')->numeric(),
            TextInput::make('quartile'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->wrap()->searchable(),
                TextColumn::make('journal'),
                TextColumn::make('year'),
                TextColumn::make('citation')->label('Sitasi'),
                TextColumn::make('quartile'),
                TextColumn::make('author_order')->label('Urutan Penulis'),
                TextColumn::make('creator')->label('Pembuat'),
                
                // PERBAIKAN: Melekatkan state nilai URL agar bisa diklik menuju tab baru
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