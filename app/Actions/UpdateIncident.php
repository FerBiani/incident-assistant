<?php

namespace App\Actions;

use App\Models\Incident;

class UpdateIncident
{
    /** @param array{project_id: int, title: string, description: string|null, logs: string|null, severity: string} $attributes */
    public function handle(Incident $incident, array $attributes): Incident
    {
        $incident->update($attributes);

        return $incident->refresh();
    }
}
