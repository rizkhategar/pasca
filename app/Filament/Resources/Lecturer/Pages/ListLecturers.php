<?php

namespace App\Filament\Resources\Lecturer\Pages;

use App\Filament\Resources\Lecturer\LecturerResource;
use Filament\Resources\Pages\ListRecords;

class ListLecturers extends ListRecords
{
    protected static string $resource = LecturerResource::class;
}
