<?php

use App\Actions\AnalyzeIncident;
use App\Actions\RequestIncidentAnalysis;
use App\Ai\Agents\IncidentTriageAgent;
use App\Enums\IncidentAnalysisStatus;
use App\Enums\IncidentSeverity;
use App\Exceptions\IncidentAnalysisAlreadyPending;
use App\Jobs\AnalyzeIncidentJob;
use App\Models\Incident;
use App\Models\IncidentNote;
use App\Models\Project;
use Illuminate\Support\Facades\Queue;

it('queues triage without running the agent while creating an incident', function () {
    Queue::fake();
    IncidentTriageAgent::fake();
    $project = Project::factory()->create();

    $response = $this->post(route('incidents.store'), [
        'project_id' => $project->id,
        'title' => 'Timeout no checkout',
        'description' => 'Clientes não concluem compras.',
        'logs' => 'GatewayTimeout',
        'severity' => IncidentSeverity::Critical->value,
    ]);

    $incident = Incident::query()->sole();
    $response->assertRedirectToRoute('incidents.show', $incident);
    expect($incident->ai_analysis_status)->toBe(IncidentAnalysisStatus::Pending);
    Queue::assertPushed(AnalyzeIncidentJob::class, fn (AnalyzeIncidentJob $job): bool => $job->incident->is($incident));
    IncidentTriageAgent::assertNeverPrompted();
});

it('queues a manual analysis and rejects a concurrent request', function () {
    Queue::fake();
    $incident = Incident::factory()->create();

    $this->post(route('incidents.analysis', $incident))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($incident->refresh()->ai_analysis_status)->toBe(IncidentAnalysisStatus::Pending);
    Queue::assertPushed(AnalyzeIncidentJob::class, 1);

    $this->post(route('incidents.analysis', $incident))
        ->assertRedirect()
        ->assertSessionHas('warning');

    Queue::assertPushed(AnalyzeIncidentJob::class, 1);
});

it('atomically refuses analysis while one is pending', function () {
    Queue::fake();
    $incident = Incident::factory()->create(['ai_analysis_status' => IncidentAnalysisStatus::Pending]);

    expect(fn () => app(RequestIncidentAnalysis::class)->handle($incident))
        ->toThrow(IncidentAnalysisAlreadyPending::class);

    Queue::assertNothingPushed();
});

it('replaces the previous analysis only after a successful job', function () {
    $incident = Incident::factory()->has(IncidentNote::factory(), 'notes')->create([
        'ai_analysis_status' => IncidentAnalysisStatus::Pending,
        'ai_summary' => 'Análise anterior',
        'ai_severity' => IncidentSeverity::Low,
        'ai_probable_causes' => ['Causa anterior'],
        'ai_recommended_actions' => ['Ação anterior'],
        'ai_analyzed_at' => now()->subDay(),
    ]);
    $untouched = $incident->only(['title', 'description', 'logs', 'severity', 'status']);
    IncidentTriageAgent::fake([[
        'summary' => 'Falha de comunicação com o gateway.',
        'severity' => 'high',
        'probable_causes' => ['Timeout no gateway'],
        'recommended_actions' => ['Verificar o gateway'],
    ]]);

    (new AnalyzeIncidentJob($incident))->handle(app(AnalyzeIncident::class));

    $incident->refresh();
    expect($incident->ai_analysis_status)->toBe(IncidentAnalysisStatus::Completed)
        ->and($incident->ai_summary)->toBe('Falha de comunicação com o gateway.')
        ->and($incident->only(array_keys($untouched)))->toEqual($untouched)
        ->and($incident->notes)->toHaveCount(1);
});

it('marks a definitively failed job without deleting the previous analysis', function () {
    $incident = Incident::factory()->create([
        'ai_analysis_status' => IncidentAnalysisStatus::Pending,
        'ai_summary' => 'Análise preservada',
        'ai_analyzed_at' => now()->subDay(),
    ]);
    $job = new AnalyzeIncidentJob($incident);

    $job->failed(new RuntimeException('provider unavailable'));

    expect($incident->refresh()->ai_analysis_status)->toBe(IncidentAnalysisStatus::Failed)
        ->and($incident->ai_summary)->toBe('Análise preservada')
        ->and(Incident::query()->whereKey($incident)->exists())->toBeTrue();
});
