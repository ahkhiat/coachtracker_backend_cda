<?php

namespace App\Enum;

enum PresenceStatusEnum: string
{
    case ON_TIME = 'on_time';
    case LATE = 'late';
    case ABSENT = 'absent';
    case EXCUSED = 'excused';
    case UNEXCUSED = 'unexcused';

    public function label(): string
    {
        return match ($this) {
            self::ON_TIME => 'À l\'heure',
            self::LATE => 'En retard',
            self::ABSENT => 'Absent',
            self::EXCUSED => 'Excusé',
            self::UNEXCUSED => 'Non justifié'
        };
    }
    
    
}