<?php

namespace App\Enum;

enum ConvocationStatusEnum: string
{
    case NOT_CALLED = 'not_called';
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::NOT_CALLED => 'Non convoqué',
            self::PENDING => 'En attente',
            self::ACCEPTED => 'Acceptée',
            self::REJECTED => 'Rejetée',
            self::CANCELLED => 'Annulée'
        };
    }
}