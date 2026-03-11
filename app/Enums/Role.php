<?php

namespace App\Enums;

/**
 * Daftar peran sistem yang dipakai untuk RBAC.
 */
enum Role: string
{
    case ADMIN = 'ADMIN';
    case CLIENT = 'CLIENT';
    case PHOTOGRAPHER = 'PHOTOGRAPHER';
    case EDITOR = 'EDITOR';
    case MANAGER = 'MANAGER';

    /**
     * Helper untuk validasi role berbasis string (mis. request/rule).
     *
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
