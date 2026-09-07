<?php

namespace App\Actions;

use App\Enums\IncidentStatus;
use App\Exceptions\InvalidIncidentStatusTransition;
use App\Models\Incident;

class StartIncidentInvestigation
{
    public function handle(Incident $incident): Incident
    {
        if ($incident->status !== IncidentStatus::Open) {
            throw new InvalidIncidentStatusTransition;
        }

        $incident->update(['status' => IncidentStatus::Investigating]);

        return $incident->refresh();
    }
}
