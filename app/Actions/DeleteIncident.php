<?php

namespace App\Actions;

use App\Models\Incident;

class DeleteIncident
{
    public function handle(Incident $incident): void
    {
        $incident->delete();
    }
}
