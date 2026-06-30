<?php

namespace App\Filament\Resources\Contacts\Pages;

use App\Filament\Resources\Contacts\ContactResource;
use App\Models\Contacts;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContact extends EditRecord
{
    protected static string $resource = ContactResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['whatsapp_admins'])) {
            $data['whatsapp_admins'] = Contacts::fallbackWhatsAppAdmins($data);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return Contacts::syncLegacyWhatsAppFields($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
