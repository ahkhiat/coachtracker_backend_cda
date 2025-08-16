<?php

namespace App\Enum;

enum ConvocationStatusEnum: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::ACCEPTED => 'Acceptée',
            self::REJECTED => 'Rejetée',
            self::CANCELLED => 'Annulée'
        };
    }
}