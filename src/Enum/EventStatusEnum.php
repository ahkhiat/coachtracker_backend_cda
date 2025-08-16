<?php

namespace App\Enum;

enum EventStatusEnum: string
{
    case UPCOMING = 'upcoming';
    case ONGOING = 'ongoing';
    case FINISHED = 'finished';
    case CANCELLED = 'cancelled';
    case POSTPONED = 'postponed';

    public function label(): string
    {
        return match ($this) {
            self::UPCOMING => 'À venir',
            self::ONGOING => 'En cours',
            self::FINISHED => 'Terminé',
            self::CANCELLED => 'Annulé',
            self::POSTPONED => 'Reporté'
        };
    }
}