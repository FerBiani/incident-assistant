<?php

namespace App\Http\Controllers;

use App\Actions\ReopenIncident;
use App\Actions\ResolveIncident;
use App\Exceptions\InvalidIncidentStatusTransition;
use App\Models\Incident;
use Illuminate\Http\RedirectResponse;

class IncidentResolutionController extends Controller
{
    public function store(Incident $incident, ResolveIncident $resolveIncident): RedirectResponse
    {
        try {
            $resolveIncident->handle($incident);
        } catch (InvalidIncidentStatusTransition $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Incidente resolvido.');
    }

    public function destroy(Incident $incident, ReopenIncident $reopenIncident): RedirectResponse
    {
        try {
            $reopenIncident->handle($incident);
        } catch (InvalidIncidentStatusTransition $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Incidente reaberto.');
    }
}
