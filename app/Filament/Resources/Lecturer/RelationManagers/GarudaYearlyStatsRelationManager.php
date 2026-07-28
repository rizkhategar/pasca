<?php

namespace App\Filament\Resources\Lecturer\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GarudaYearlyStatsRelationManager extends RelationManager
{
    protected static string $relationship = 'garudaYearlyStats';
    protected static ?string $title = 'Statistik Tahunan Garuda';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('year')->label('Tahun')->required(),
            TextInput::make('articles')->numeric()->label('Artikel')->default(0)->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('year')
            ->columns([
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('articles')->label('Jumlah Artikel')->sortable(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
