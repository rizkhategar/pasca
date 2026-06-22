<?php

namespace App\Filament\Resources\VisionMissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VisionMissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vision')
                    ->label('Vision')
                    ->html()
                    ->limit(50)
                    ->weight('bold'),
                TextColumn::make('mission')
                    ->label('Mission')
                    ->html()
                    ->limit(50),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->icon('heroicon-o-trash'),
                ]),
            ])
            ->paginated(false);
    }
}
