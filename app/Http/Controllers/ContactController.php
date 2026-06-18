<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
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

        return view('contact.index', compact('whatsappAdmins'));
    }
}
