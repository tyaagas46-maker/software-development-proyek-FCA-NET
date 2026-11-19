<?php

namespace App\Support;

class PhoneHelper
{
    /**
     * Jika input berbentuk nomor HP Indonesia (08..., 628..., +628...),
     * seragamkan ke format 08...
     * Jika username teks biasa (misal "admin"), biarkan apa adanya.
     */
    public static function normalizeLoginIdentifier(string $input): string
    {
        $trimmed = trim($input);
        $cleaned = preg_replace('/[\s\-\.\(\)]+/', '', $trimmed);

        if (preg_match('/^(\+?62|0)(8[0-9]{7,13})$/', $cleaned, $matches)) {
            return '0' . $matches[2];
        }

        return $trimmed;
    }

    /**
     * Nanti terpakai untuk link pengingat WhatsApp: https://wa.me/628...
     */
    public static function toWhatsAppFormat(string $noHp): string
    {
        $normalized = self::normalizeLoginIdentifier($noHp);

        if (str_starts_with($normalized, '0')) {
            return '62' . substr($normalized, 1);
        }

        return $normalized;
    }
}