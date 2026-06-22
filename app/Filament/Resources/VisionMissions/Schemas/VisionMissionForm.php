<?php

namespace App\Filament\Resources\VisionMissions\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VisionMissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Hero Section')
                    ->description('Manage the heading shown at the top of the Vision & Mission page.')
                    ->icon('heroicon-o-photo')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('hero_title')
                            ->label('Hero Title')
                            ->default('Vision & Mission')
                            ->maxLength(255),
                        TextInput::make('hero_subtitle')
                            ->label('Hero Subtitle')
                            ->default('Postgraduate School Universitas Ngudi Waluyo')
                            ->maxLength(255),
                    ]),

                Section::make('Vision & Mission')
                    ->description('Manage the Vision and Mission content for the postgraduate profile page.')
                    ->icon('heroicon-o-document-text')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('vision_title')
                            ->label('Vision Title')
                            ->default('Vision')
                            ->required()
                            ->maxLength(255),
                        RichEditor::make('vision')
                            ->required()
                            ->label('Vision Content')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                            ]),
                        TextInput::make('mission_title')
                            ->label('Mission Title')
                            ->default('Mission')
                            ->required()
                            ->maxLength(255),
                        RichEditor::make('mission')
                            ->required()
                            ->label('Mission Content')
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
                        TextInput::make('objectives_title')
                            ->required()
                            ->default('Objectives')
                            ->label('Objectives Title'),
                        RichEditor::make('objectives')
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
                        TextInput::make('field_objectives_title')
                            ->required()
                            ->default('UNW Field Objectives')
                            ->label('Field Objectives Title'),
                        RichEditor::make('field_objectives')
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
                        TextInput::make('goals_targets_title')
                            ->required()
                            ->default('Goals and Targets')
                            ->label('Goals & Targets Title'),
                        RichEditor::make('goals_targets')
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
