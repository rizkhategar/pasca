<?php

namespace App\Filament\Resources\DetailDosens\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class ServiceYearliesRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceYearlies';
    protected static ?string $title = 'Grafik Pengabdian Tahunan';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            // Mengubah 'tahun' menjadi 'year'
            TextInput::make('year')->label('Tahun')->required(),
            // Mengubah 'jumlah' menjadi 'count'
            TextInput::make('count')->numeric()->default(0)->required()->label('Jumlah Pengabdian'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // Mengubah record title attribute menjadi 'year'
            ->recordTitleAttribute('year')
            ->columns([
                // Penyesuaian nama kolom ke Bahasa Inggris dengan label tetap Bahasa Indonesia
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('count')->label('Jumlah Pengabdian')->sortable(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}