<?php

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\IncidentNote;
use App\Models\Project;
use Inertia\Testing\AssertableInertia as Assert;

it('renders incident creation guidance when no project exists', function () {
    $response = $this->get(route('incidents.create'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Incidents/Create')
        ->where('canCreate', false)
        ->has('projects', 0));
});

it('renders alphabetic project options and every severity on the creation form', function () {
    $zulu = Project::factory()->create(['name' => 'Zulu']);
    $alpha = Project::factory()->create(['name' => 'Alpha']);

    $response = $this->get(route('incidents.create'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Incidents/Create')
        ->where('canCreate', true)
        ->where('projects', [
            ['value' => $alpha->id, 'label' => 'Alpha'],
            ['value' => $zulu->id, 'label' => 'Zulu'],
        ])
        ->where('severities', [
            ['value' => 'low', 'label' => 'Baixa'],
            ['value' => 'medium', 'label' => 'Média'],
            ['value' => 'high', 'label' => 'Alta'],
            ['value' => 'critical', 'label' => 'Crítica'],
        ]));
});

it('creates an open incident without accepting status from the payload', function () {
    $project = Project::factory()->create();

    $response = $this->post(route('incidents.store'), [
        'project_id' => $project->id,
        'title' => 'Timeout no checkout',
        'description' => '',
        'logs' => "line one\nline two",
        'severity' => IncidentSeverity::Critical->value,
        'status' => IncidentStatus::Resolved->value,
    ]);

    $incident = Incident::query()->sole();
    $response->assertRedirectToRoute('incidents.show', $incident)
        ->assertSessionHas('success');
    expect($incident->status)->toBe(IncidentStatus::Open)
        ->and($incident->description)->toBeNull();
});

it('validates required incident fields and accepted severity values', function (array $payload, array $errors) {
    Project::factory()->create();

    $response = $this->post(route('incidents.store'), $payload);

    $response->assertSessionHasErrors($errors);
    $this->assertDatabaseCount('incidents', 0);
})->with([
    'required fields' => [[], ['project_id', 'title', 'severity']],
    'invalid project and severity' => [[
        'project_id' => 999999,
        'title' => 'Falha',
        'severity' => 'urgent',
    ], ['project_id', 'severity']],
]);

it('rejects incident text beyond its storage limit', function () {
    $project = Project::factory()->create();

    $response = $this->post(route('incidents.store'), [
        'project_id' => $project->id,
        'title' => str_repeat('a', 256),
        'severity' => IncidentSeverity::Low->value,
    ]);

    $response->assertSessionHasErrors([
        'title' => 'O título do incidente deve ter no máximo 255 caracteres.',
    ]);
    $this->assertDatabaseCount('incidents', 0);
});

it('shows the complete incident contract', function () {
    $incident = Incident::factory()->high()->investigating()->create([
        'title' => 'Fila indisponível',
        'description' => 'O consumidor parou de processar.',
        'logs' => "Exception\n  at Worker.php:42",
    ]);
    $note = IncidentNote::factory()->for($incident)->create(['content' => 'Reinício não resolveu.']);

    $response = $this->get(route('incidents.show', $incident));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Incidents/Show')
        ->where('incident.id', $incident->id)
        ->where('incident.project.id', $incident->project_id)
        ->where('incident.description', 'O consumidor parou de processar.')
        ->where('incident.logs', "Exception\n  at Worker.php:42")
        ->where('incident.severity', 'high')
        ->where('incident.status', 'investigating')
        ->where('incident.notes.0.id', $note->id));
});

it('updates and transfers an incident without changing its notes or status', function () {
    $source = Project::factory()->create();
    $target = Project::factory()->create();
    $incident = Incident::factory()->for($source)->investigating()->create();
    $note = IncidentNote::factory()->for($incident)->create();

    $response = $this->put(route('incidents.update', $incident), [
        'project_id' => $target->id,
        'title' => 'Falha atualizada',
        'description' => 'Novo contexto',
        'logs' => null,
        'severity' => IncidentSeverity::High->value,
        'status' => IncidentStatus::Resolved->value,
    ]);

    $response->assertRedirectToRoute('incidents.show', $incident);
    $incident->refresh();
    expect($incident->project->is($target))->toBeTrue()
        ->and($incident->status)->toBe(IncidentStatus::Investigating)
        ->and($incident->notes()->sole()->is($note))->toBeTrue();

    $this->get(route('projects.show', $source))->assertInertia(fn (Assert $page) => $page
        ->has('incidents.data', 0));
    $this->get(route('projects.show', $target))->assertInertia(fn (Assert $page) => $page
        ->has('incidents.data', 1)
        ->where('incidents.data.0.id', $incident->id));
});

it('rejects invalid incident updates without changing stored data', function () {
    $incident = Incident::factory()->create();
    $originalAttributes = $incident->getAttributes();

    $response = $this->put(route('incidents.update', $incident), [
        'project_id' => 999999,
        'title' => '',
        'severity' => 'urgent',
    ]);

    $response->assertSessionHasErrors(['project_id', 'title', 'severity']);
    expect($incident->refresh()->getAttributes())->toEqual($originalAttributes);
});

it('deletes an incident and its notes', function () {
    $incident = Incident::factory()->create();
    $note = IncidentNote::factory()->for($incident)->create();

    $response = $this->delete(route('incidents.destroy', $incident));

    $response->assertRedirectToRoute('incidents.index')->assertSessionHas('success');
    $this->assertModelMissing($incident);
    $this->assertModelMissing($note);
});

it('filters incidents with and semantics and preserves the query string', function () {
    $project = Project::factory()->create();
    $matching = Incident::factory()->for($project)->critical()->resolved()->create();
    Incident::factory()->for($project)->critical()->create();
    Incident::factory()->critical()->resolved()->create();

    $response = $this->get(route('incidents.index', [
        'project_id' => $project->id,
        'severity' => IncidentSeverity::Critical->value,
        'status' => IncidentStatus::Resolved->value,
    ]));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Incidents/Index')
        ->has('incidents.data', 1)
        ->where('incidents.data.0.id', $matching->id)
        ->where('filters.project_id', $project->id)
        ->where('filters.severity', 'critical')
        ->where('filters.status', 'resolved')
        ->where('incidents.meta.total', 1));
});

it('applies each incident filter independently', function () {
    $firstProject = Project::factory()->create();
    $secondProject = Project::factory()->create();
    $first = Incident::factory()->for($firstProject)->low()->create();
    $second = Incident::factory()->for($secondProject)->critical()->resolved()->create();

    $this->get(route('incidents.index', ['project_id' => $firstProject->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('incidents.data', 1)
            ->where('incidents.data.0.id', $first->id));

    $this->get(route('incidents.index', ['severity' => 'critical']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('incidents.data', 1)
            ->where('incidents.data.0.id', $second->id));

    $this->get(route('incidents.index', ['status' => 'resolved']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('incidents.data', 1)
            ->where('incidents.data.0.id', $second->id));
});

it('paginates incidents while retaining active filters', function () {
    $project = Project::factory()->create();
    Incident::factory()->count(16)->for($project)->critical()->resolved()->create();
    Incident::factory()->for($project)->low()->create();

    $response = $this->get(route('incidents.index', [
        'project_id' => $project->id,
        'severity' => 'critical',
        'status' => 'resolved',
    ]));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('incidents.data', 15)
        ->where('incidents.meta.total', 16)
        ->where('incidents.meta.last_page', 2)
        ->where('incidents.links.next', fn (string $url): bool => str_contains($url, 'project_id='.$project->id)
            && str_contains($url, 'severity=critical')
            && str_contains($url, 'status=resolved')));
});

it('rejects invalid incident filters', function () {
    $response = $this->get(route('incidents.index', [
        'project_id' => 999999,
        'severity' => 'urgent',
        'status' => 'closed',
    ]));

    $response->assertSessionHasErrors(['project_id', 'severity', 'status']);
});

it('orders incidents newest first with a stable id tiebreaker', function () {
    $createdAt = now()->subHour();
    $olderId = Incident::factory()->create(['created_at' => $createdAt]);
    $newerId = Incident::factory()->create(['created_at' => $createdAt]);

    $response = $this->get(route('incidents.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('incidents.data.0.id', $newerId->id)
        ->where('incidents.data.1.id', $olderId->id));
});
