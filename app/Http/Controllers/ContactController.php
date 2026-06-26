<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        $contact = Contact::query()->latest('updated_at')->latest('id')->first();

        $whatsappAdmins = $contact?->resolvedWhatsAppAdmins() ?? Contact::fallbackWhatsAppAdmins([
            'primary_admin_name' => 'Admin 1',
            'primary_whatsapp' => '+62 857-3033-9469',
            'secondary_admin_name' => 'Admin 2',
            'secondary_whatsapp' => '+62 811-2758-575',
        ]);

        $whatsappAdmins = collect($whatsappAdmins)
            ->map(fn (array $admin): array => [
                ...$admin,
                'url' => $admin['url'] ?? Contact::whatsappUrl($admin['number'] ?? null),
            ])
            ->filter(fn (array $admin): bool => ! empty($admin['url']))
            ->values()
            ->all();

        return response()->view('contact.index', compact('contact', 'whatsappAdmins'));
    }
}
