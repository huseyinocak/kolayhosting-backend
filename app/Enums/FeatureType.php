<?php

namespace App\Enums;

enum FeatureType: string
{
    case BOOLEAN = 'boolean';
    case NUMERIC = 'numeric';
    case TEXT = 'text';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
