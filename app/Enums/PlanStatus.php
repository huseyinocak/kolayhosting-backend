<?php

namespace App\Enums;

enum PlanStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';

    /**
     * Tüm plan durumlarını içeren bir dizi döndürür.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
