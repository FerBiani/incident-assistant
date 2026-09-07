<?php

namespace App\Actions;

use App\Enums\IncidentStatus;
use App\Exceptions\InvalidIncidentStatusTransition;
use App\Models\Incident;

class ReopenIncident
{
    public function handle(Incident $incident): Incident
    {
        if ($incident->status !== IncidentStatus::Resolved) {
            throw new InvalidIncidentStatusTransition;
        }

        $incident->update(['status' => IncidentStatus::Open]);

        return $incident->refresh();
    }
}
