<?php

namespace App\Jobs;

use App\Actions\AnalyzeIncident;
use App\Enums\IncidentAnalysisStatus;
use App\Models\Incident;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnalyzeIncidentJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 150;

    public bool $failOnTimeout = true;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(public Incident $incident) {}

    /**
     * Execute the job.
     */
    public function handle(AnalyzeIncident $analyzeIncident): void
    {
        $analyzeIncident->handle($this->incident);
    }

    public function failed(?\Throwable $exception): void
    {
        Incident::query()
            ->whereKey($this->incident->getKey())
            ->where('ai_analysis_status', IncidentAnalysisStatus::Pending->value)
            ->update(['ai_analysis_status' => IncidentAnalysisStatus::Failed]);
    }
}
