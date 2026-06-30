<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Contacts extends Model
{
    protected $table = 'contacts';

    protected $fillable = [
        'primary_admin_name',
        'primary_whatsapp',
        'secondary_admin_name',
        'secondary_whatsapp',
        'whatsapp_admins',
    ];

    protected function casts(): array
    {
        return [
            'whatsapp_admins' => 'array',
        ];
    }

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

    public static function sanitizeWhatsAppAdmins(mixed $admins): array
    {
        return collect(is_array($admins) ? $admins : [])
            ->map(function (mixed $admin, int $index): ?array {
                if (! is_array($admin)) {
                    return null;
                }

                $number = trim((string) ($admin['number'] ?? $admin['whatsapp'] ?? ''));

                if ($number === '') {
                    return null;
                }

                return [
                    'name' => trim((string) ($admin['name'] ?? '')) ?: 'Admin ' . ($index + 1),
                    'number' => $number,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public static function fallbackWhatsAppAdmins(array $attributes): array
    {
        return self::sanitizeWhatsAppAdmins([
            [
                'name' => $attributes['primary_admin_name'] ?? 'Admin 1',
                'number' => $attributes['primary_whatsapp'] ?? '+62 857-3033-9469',
            ],
            [
                'name' => $attributes['secondary_admin_name'] ?? 'Admin 2',
                'number' => $attributes['secondary_whatsapp'] ?? '+62 811-2758-575',
            ],
        ]);
    }

    public static function syncLegacyWhatsAppFields(array $data): array
    {
        $admins = self::sanitizeWhatsAppAdmins($data['whatsapp_admins'] ?? []);

        if ($admins === []) {
            $admins = self::fallbackWhatsAppAdmins($data);
        }

        $data['whatsapp_admins'] = $admins;
        $data['primary_admin_name'] = $admins[0]['name'] ?? 'Admin 1';
        $data['primary_whatsapp'] = $admins[0]['number'] ?? '+62 857-3033-9469';
        $data['secondary_admin_name'] = $admins[1]['name'] ?? 'Admin 2';
        $data['secondary_whatsapp'] = $admins[1]['number'] ?? '+62 811-2758-575';

        return $data;
    }

    public function resolvedWhatsAppAdmins(): array
    {
        $admins = self::sanitizeWhatsAppAdmins($this->whatsapp_admins);

        if ($admins === []) {
            $admins = self::fallbackWhatsAppAdmins($this->attributesToArray());
        }

        return collect($admins)
            ->map(fn (array $admin): array => [
                ...$admin,
                'url' => self::whatsappUrl($admin['number']),
            ])
            ->filter(fn (array $admin): bool => ! empty($admin['url']))
            ->values()
            ->all();
    }
}
