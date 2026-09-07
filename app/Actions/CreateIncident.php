<?php

namespace App\Actions;

use App\Enums\IncidentStatus;
use App\Models\Incident;

class CreateIncident
{
    /** @param array{project_id: int, title: string, description: string|null, logs: string|null, severity: string} $attributes */
    public function handle(array $attributes): Incident
    {
        return Incident::query()->create([
            ...$attributes,
            'status' => IncidentStatus::Open,
        ]);
    }
}
