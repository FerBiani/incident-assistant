<?php

namespace App\Enums;

enum IncidentStatus: string
{
    case Open = 'open';
    case Investigating = 'investigating';
    case Resolved = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Aberto',
            self::Investigating => 'Em investigação',
            self::Resolved => 'Resolvido',
        };
    }
}
