<?php

namespace App\Actions;

use App\Ai\Agents\IncidentTriageAgent;
use App\Enums\IncidentAnalysisStatus;
use App\Models\Incident;
use Illuminate\Support\Facades\Log;

class AnalyzeIncident
{
    public function handle(Incident $incident): void
    {
        $prompt = <<<PROMPT
        Title: {$incident->title}

        Description:
        {$incident->description}

        Logs / Stack trace:
        {$incident->logs}

        Analisys notes:
        {$incident->notes->pluck('content')->join('; ')}
        PROMPT;

        $response = (new IncidentTriageAgent)->prompt($prompt);

        Log::debug('AgentResponse', [
            'response' => $response->text,
        ]);

        $incident->update([
            'ai_summary' => data_get($response, 'summary'),
            'ai_severity' => data_get($response, 'severity'),
            'ai_probable_causes' => data_get($response, 'probable_causes'),
            'ai_recommended_actions' => data_get($response, 'recommended_actions'),
            'ai_analyzed_at' => now(),
            'ai_analysis_status' => IncidentAnalysisStatus::Completed,
        ]);
    }
}
