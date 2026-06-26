<?php

namespace App\Filament\Resources\AboutPostgraduates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AboutPostgraduatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subheading')->label('Sub Judul')->weight('bold'),
                TextColumn::make('heading')->label('Judul Utama')->limit(40),
                TextColumn::make('updated_at')->label('Terakhir Diubah')->dateTime('d M Y, H:i')->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->icon('heroicon-o-trash'),
                ]),
            ])
            ->paginated(false);
    }
}
