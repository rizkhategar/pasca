<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        $contact = Contact::query()->latest('updated_at')->latest('id')->first();

        $whatsappAdmins = $contact
            ? collect($contact->resolvedWhatsAppAdmins())
                ->map(fn (array $admin): array => [
                    ...$admin,
                    'url' => $admin['url'] ?? Contact::whatsappUrl($admin['number'] ?? null),
                ])
                ->filter(fn (array $admin): bool => ! empty($admin['url']))
                ->values()
                ->all()
            : [];

        return response()->view('contact.index', compact('contact', 'whatsappAdmins'));
    }
}
