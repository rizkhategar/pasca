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

class ResearchesRelationManager extends RelationManager
{
    protected static string $relationship = 'researches';
    protected static ?string $title = 'Data Penelitian';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('title')->label('Judul')->required()->columnSpanFull(),
            TextInput::make('leader')->label('Leader'),
            TextInput::make('scheme')->label('Skema'),
            TextInput::make('personnel')->label('Personil/Anggota'),
            TextInput::make('year')->label('Tahun'),
            TextInput::make('funding')->label('Dana'),
            TextInput::make('status')->label('Status'),
            TextInput::make('source')->label('Sumber Dana'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label('Judul')->wrap()->searchable(),
                TextColumn::make('leader')->label('Leader'),
                TextColumn::make('scheme')->label('Skema'),
                TextColumn::make('personnel')->label('Personil/Anggota'),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('funding')->label('Dana'),
                TextColumn::make('status')->label('Status'),
                TextColumn::make('source')->label('Sumber Dana'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
