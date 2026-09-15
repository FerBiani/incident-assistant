<?php

namespace App\Actions;

use App\Enums\IncidentNoteSource;
use App\Models\Incident;
use App\Models\IncidentNote;

class AddIncidentNote
{
    public function handle(
        Incident $incident,
        string $content,
        IncidentNoteSource $source = IncidentNoteSource::User,
    ): IncidentNote {
        return $incident->notes()->create([
            'content' => $content,
            'source' => $source,
        ]);
    }
}
