<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        $contact = Contact::query()->latest('updated_at')->latest('id')->first();

        $whatsappAdmins = [
            [
                'name' => $contact?->primary_admin_name ?: 'Admin 1',
                'number' => $contact?->primary_whatsapp ?: '+62 857-3033-9469',
                'url' => Contact::whatsappUrl($contact?->primary_whatsapp ?: '+62 857-3033-9469'),
            ],
            [
                'name' => $contact?->secondary_admin_name ?: 'Admin 2',
                'number' => $contact?->secondary_whatsapp ?: '+62 811-2758-575',
                'url' => Contact::whatsappUrl($contact?->secondary_whatsapp ?: '+62 811-2758-575'),
            ],
        ];

        $viewData = compact('whatsappAdmins');
        $page = view('contact.index', $viewData)->render();
        $modal = view('component.contact-whatsapp-modal', $viewData)->render();
        $primaryNumber = htmlspecialchars((string) $whatsappAdmins[0]['number'], ENT_QUOTES, 'UTF-8');
        $primaryUrl = (string) $whatsappAdmins[0]['url'];

        // Existing Contact markup keeps its layout while values come from Filament settings.
        $page = str_replace('https://wa.me/6285730339469', $primaryUrl, $page);
        $page = str_replace('+62 857-3033-9469', $primaryNumber, $page);

        return response(str_replace('</body>', $modal . '</body>', $page));
    }
}
