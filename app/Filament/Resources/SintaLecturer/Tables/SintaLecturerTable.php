<?php

namespace App\Filament\Resources\SintaLecturer\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SintaLecturerTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sinta_id')
                    ->label('SINTA ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department')
                    ->label('Departemen')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('scopus_h_index')
                    ->label('Scopus H-Index')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('google_scholar_h_index')
                    ->label('Scholar H-Index')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('sinta_score_3yr')
                    ->label('SINTA Score 3Yr')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('sinta_score')
                    ->label('SINTA Score')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('affiliation_score_3yr')
                    ->label('Affiliation 3Yr')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('affiliation_score')
                    ->label('Affiliation')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('has_detail')
                    ->label('Detail')
                    ->state(fn ($record): bool => (bool) $record->detail)
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                //
            ])
            ->actions([
                // Master SINTA lecturers are synced from SINTA, not edited from this table.
            ])
            ->toolbarActions([
                //
            ]);
    }
}
