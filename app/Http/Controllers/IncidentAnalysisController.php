<?php

namespace App\Http\Controllers;

use App\Actions\AnalyzeIncident;
use App\Models\Incident;
use Illuminate\Http\RedirectResponse;

class IncidentAnalysisController extends Controller
{
    public function __invoke(Incident $incident, AnalyzeIncident $analyzeIncident): RedirectResponse
    {
        $analyzeIncident->handle($incident);

        return back()->with('success', 'Incidente analisado com sucesso!');
    }
}