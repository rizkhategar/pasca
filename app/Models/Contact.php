<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Contact extends Model
{
    protected $fillable = [
        'primary_admin_name',
        'primary_whatsapp',
        'secondary_admin_name',
        'secondary_whatsapp',
    ];

    public static function whatsappUrl(?string $number): ?string
    {
        $normalized = self::normalizeWhatsAppNumber($number);

        return $normalized ? 'https://wa.me/' . $normalized : null;
    }

    public static function normalizeWhatsAppNumber(?string $number): ?string
    {
        $number = preg_replace('/\D+/', '', (string) $number);

        if ($number === '') {
            return null;
        }

        if (Str::startsWith($number, '0')) {
            $number = '62' . substr($number, 1);
        } elseif (Str::startsWith($number, '8')) {
            $number = '62' . $number;
        }

        return $number;
    }
}
