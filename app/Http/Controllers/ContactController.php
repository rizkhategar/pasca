<?php

namespace App\Http\Controllers;

use App\Models\Contacts;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        $contact = Contacts::query()->latest('updated_at')->latest('id')->first();

        $whatsappAdmins = $contact
            ? collect($contact->resolvedWhatsAppAdmins())
                ->map(fn (array $admin): array => [
                    ...$admin,
                    'url' => $admin['url'] ?? Contacts::whatsappUrl($admin['number'] ?? null),
                ])
                ->filter(fn (array $admin): bool => ! empty($admin['url']))
                ->values()
                ->all()
            : [];

        return response()->view('contact.index', compact('contact', 'whatsappAdmins'));
    }
}
