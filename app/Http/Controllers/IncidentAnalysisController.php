<?php

namespace App\Http\Controllers;

use App\Actions\RequestIncidentAnalysis;
use App\Exceptions\IncidentAnalysisAlreadyPending;
use App\Models\Incident;
use Illuminate\Http\RedirectResponse;

class IncidentAnalysisController extends Controller
{
    public function __invoke(Incident $incident, RequestIncidentAnalysis $requestIncidentAnalysis): RedirectResponse
    {
        try {
            $requestIncidentAnalysis->handle($incident);
        } catch (IncidentAnalysisAlreadyPending) {
            return back()->with('warning', 'Uma análise deste incidente já está em andamento.');
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Não foi possível iniciar a análise. Tente novamente.');
        }

        return back()->with('success', 'Análise iniciada em segundo plano.');
    }
}
