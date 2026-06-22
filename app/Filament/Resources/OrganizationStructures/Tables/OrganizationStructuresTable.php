<?php

namespace App\Filament\Resources\OrganizationStructures\Tables;

use App\Filament\Resources\OrganizationStructures\OrganizationStructureResource;
use App\Models\OrganizationStructure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class OrganizationStructuresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->getStateUsing(fn (OrganizationStructure $record): ?string => $record->image_path && Storage::disk('public')->exists($record->image_path)
                        ? route('organization-structures.image', $record)
                        : null)
                    ->height(72)
                    ->width(110)
                    ->square(false),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('image_path')
                    ->label('Path')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
