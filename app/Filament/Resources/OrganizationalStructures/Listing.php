<?php

namespace App\Filament\Resources\OrganizationalStructures;

use App\Models\OrganizationalStructure;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class Listing
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->getStateUsing(fn (OrganizationalStructure $record): ?string => $record->image_path
                        ? url(route('organization-structures.image', $record, false)) . '?v=' . optional($record->updated_at)->timestamp
                        : null)
                    ->height(80)
                    ->width(120)
                    ->square(false),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

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
            ]);
    }
}
