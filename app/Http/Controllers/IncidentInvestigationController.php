<?php

namespace App\Http\Controllers;

use App\Actions\StartIncidentInvestigation;
use App\Exceptions\InvalidIncidentStatusTransition;
use App\Models\Incident;
use Illuminate\Http\RedirectResponse;

class IncidentInvestigationController extends Controller
{
    public function store(Incident $incident, StartIncidentInvestigation $startInvestigation): RedirectResponse
    {
        try {
            $startInvestigation->handle($incident);
        } catch (InvalidIncidentStatusTransition $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Investigação iniciada.');
    }
}
