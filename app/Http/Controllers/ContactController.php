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

        return response(str_replace('</body>', $modal . '</body>', $page));
    }
}
