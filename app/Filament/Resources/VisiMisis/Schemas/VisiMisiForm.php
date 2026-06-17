<?php

namespace App\Filament\Resources\VisiMisis\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;

class VisiMisiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Vision & Mission')
                    ->description('Manage the Vision and Mission content for the postgraduate profile page.')
                    ->icon('heroicon-o-document-text')
                    ->columnSpanFull()
                    ->schema([
                        RichEditor::make('visi')
                            ->required()
                            ->label('Vision')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                            ]),
                        RichEditor::make('misi')
                            ->required()
                            ->label('Mission')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                            ]),
                    ]),

                Section::make('Objectives')
                    ->description('Manage the title and content for the Objectives section.')
                    ->icon('heroicon-o-academic-cap')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('judul_tujuan')
                            ->required()
                            ->default('Objectives')
                            ->label('Objectives Title'),
                        RichEditor::make('tujuan')
                            ->required()
                            ->label('Objectives Content')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                            ]),
                    ]),

                Section::make('Field Objectives')
                    ->description('Manage the title and content for the Field Objectives section.')
                    ->icon('heroicon-o-briefcase')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('judul_tujuan_bidang')
                            ->required()
                            ->default('UNW Field Objectives')
                            ->label('Field Objectives Title'),
                        RichEditor::make('tujuan_bidang')
                            ->required()
                            ->label('Field Objectives Content')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                            ]),
                    ]),

                Section::make('Goals & Targets')
                    ->description('Manage the title and content for the Goals and Targets section.')
                    ->icon('heroicon-o-flag')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('judul_sasaran_target')
                            ->required()
                            ->default('Goals and Targets')
                            ->label('Goals & Targets Title'),
                        RichEditor::make('sasaran_target')
                            ->required()
                            ->label('Goals & Targets Content')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                            ]),
                    ]),
            ]);
    }
}
