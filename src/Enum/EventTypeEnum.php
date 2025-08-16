<?php

namespace App\Enum;

enum EventTypeEnum: string
{
    case MATCH = 'match';
    case TRAINING = 'training';
    case COURSE = 'course';

    public function label(): string
    {
        return match ($this) {
            self::MATCH => 'Match',
            self::TRAINING => 'Entraînement',
            self::COURSE => 'Stage'
        };
    }
    
}