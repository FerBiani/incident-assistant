<?php

namespace App\Actions;

use App\Enums\IncidentStatus;
use App\Exceptions\InvalidIncidentStatusTransition;
use App\Models\Incident;

class ResolveIncident
{
    public function handle(Incident $incident): Incident
    {
        if (! in_array($incident->status, [IncidentStatus::Open, IncidentStatus::Investigating], true)) {
            throw new InvalidIncidentStatusTransition;
        }

        $incident->update(['status' => IncidentStatus::Resolved]);

        return $incident->refresh();
    }
}
