<?php

namespace App\Actions;

use App\Models\Incident;
use App\Models\IncidentNote;

class AddIncidentNote
{
    public function handle(Incident $incident, string $content): IncidentNote
    {
        return $incident->notes()->create([
            'content' => $content,
            'source' => 'user',
        ]);
    }
}
