<?php

namespace App\Actions;

use App\Enums\IncidentStatus;
use App\Models\Incident;
use Throwable;

class CreateIncident
{
    public function __construct(private readonly RequestIncidentAnalysis $requestIncidentAnalysis) {}

    /** @param array{project_id: int, title: string, description: string|null, logs: string|null, severity: string} $attributes */
    public function handle(array $attributes): Incident
    {
        $incident = Incident::query()->create([
            ...$attributes,
            'status' => IncidentStatus::Open,
        ]);

        try {
            $this->requestIncidentAnalysis->handle($incident);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $incident;
    }
}
