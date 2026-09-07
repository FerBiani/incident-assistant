<?php

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\IncidentNote;
use App\Models\Project;
use Illuminate\Database\QueryException;

it('persists the incident domain with typed relationships and defaults', function () {
    $project = Project::factory()->create();
    $incident = Incident::factory()->for($project)->create();
    $note = IncidentNote::factory()->for($incident)->create();

    expect($incident->project->is($project))->toBeTrue()
        ->and($project->incidents->first()->is($incident))->toBeTrue()
        ->and($note->incident->is($incident))->toBeTrue()
        ->and($incident->severity)->toBeInstanceOf(IncidentSeverity::class)
        ->and($incident->status)->toBe(IncidentStatus::Open);
});

it('restricts project deletion while cascading notes with their incident', function () {
    $project = Project::factory()->create();
    $incident = Incident::factory()->for($project)->create();
    $note = IncidentNote::factory()->for($incident)->create();

    expect(fn () => $project->delete())->toThrow(QueryException::class);

    $incident->delete();

    $this->assertModelExists($project);
    $this->assertModelMissing($note);
});

it('provides named factory states for every severity', function (string $state, IncidentSeverity $severity) {
    $incident = Incident::factory()->{$state}()->create();

    expect($incident->severity)->toBe($severity);
})->with([
    'low' => ['low', IncidentSeverity::Low],
    'medium' => ['medium', IncidentSeverity::Medium],
    'high' => ['high', IncidentSeverity::High],
    'critical' => ['critical', IncidentSeverity::Critical],
]);
