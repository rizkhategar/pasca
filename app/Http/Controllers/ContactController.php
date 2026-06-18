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

        $viewData = compact('whatsappAdmins');
        $page = view('contact.index', $viewData)->render();
        $modal = view('component.contact-whatsapp-modal', $viewData)->render();
        $primaryNumber = htmlspecialchars((string) data_get($whatsappAdmins, '0.number', '+62 857-3033-9469'), ENT_QUOTES, 'UTF-8');
        $primaryUrl = (string) data_get($whatsappAdmins, '0.url', 'https://wa.me/6285730339469');

        // Preserve the current Contact Blade layout while reading live values from Filament settings.
        $page = str_replace('https://wa.me/6285730339469', $primaryUrl, $page);
        $page = str_replace('+62 857-3033-9469', $primaryNumber, $page);

        return response(str_replace('</body>', $modal . '</body>', $page));
    }
}
