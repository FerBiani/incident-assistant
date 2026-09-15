<?php

namespace App\Actions;

use App\Enums\IncidentAnalysisStatus;
use App\Exceptions\IncidentAnalysisAlreadyPending;
use App\Jobs\AnalyzeIncidentJob;
use App\Models\Incident;
use Throwable;

class RequestIncidentAnalysis
{
    public function handle(Incident $incident): void
    {
        $updated = Incident::query()
            ->whereKey($incident->getKey())
            ->where('ai_analysis_status', '!=', IncidentAnalysisStatus::Pending->value)
            ->update(['ai_analysis_status' => IncidentAnalysisStatus::Pending]);

        if ($updated === 0) {
            throw new IncidentAnalysisAlreadyPending;
        }

        $incident->refresh();

        try {
            AnalyzeIncidentJob::dispatch($incident);
        } catch (Throwable $exception) {
            Incident::query()
                ->whereKey($incident->getKey())
                ->where('ai_analysis_status', IncidentAnalysisStatus::Pending->value)
                ->update(['ai_analysis_status' => IncidentAnalysisStatus::Failed]);

            throw $exception;
        }
    }
}
