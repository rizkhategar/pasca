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

class ScholarYearlyStatsRelationManager extends RelationManager
{
    protected static string $relationship = 'scholarYearlyStats';
    protected static ?string $title = 'Statistik Tahunan Google Scholar';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('year')->label('Tahun')->required(),
            TextInput::make('publications')->numeric()->label('Publikasi')->default(0),
            TextInput::make('citations')->numeric()->label('Sitasi')->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('year')
            ->columns([
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('publications')->label('Publikasi')->sortable(),
                TextColumn::make('citations')->label('Sitasi')->sortable(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
