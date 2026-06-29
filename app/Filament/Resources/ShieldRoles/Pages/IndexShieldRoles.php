<?php

namespace App\Filament\Resources\ShieldRoles\Pages;

use App\Filament\Resources\ShieldRoles\ShieldRoleResource;
use Filament\Resources\Pages\ListRecords;

class IndexShieldRoles extends ListRecords
{
    protected static string $resource = ShieldRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
