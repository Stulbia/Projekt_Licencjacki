<?php

namespace App\Enum;

enum ReadingStatus: string
{
    case READ = 'read';
    case READING = 'reading';
    case TO_READ = 'to_read';

    public function label(): string
    {
        return match ($this) {
            self::READ => 'read',
            self::READING => 'reading',
            self::TO_READ => 'to_read',
        };
    }
}
