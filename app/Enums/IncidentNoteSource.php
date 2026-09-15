<?php

namespace App\Enums;

enum IncidentNoteSource: string
{
    case User = 'user';
    case Ai = 'ai';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Usuário',
            self::Ai => 'IA',
        };
    }
}
