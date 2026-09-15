<?php

use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\IncidentNote;
use Inertia\Testing\AssertableInertia as Assert;

it('starts an investigation only for an open incident', function () {
    $incident = Incident::factory()->create();

    $response = $this->post(route('incidents.investigation.store', $incident));

    $response->assertRedirect()->assertSessionHas('success');
    expect($incident->refresh()->status)->toBe(IncidentStatus::Investigating);
});

it('resolves open and investigating incidents', function (string $state) {
    $incident = Incident::factory()->{$state}()->create(['title' => 'Título preservado']);
    $note = IncidentNote::factory()->for($incident)->create();

    $response = $this->post(route('incidents.resolution.store', $incident));

    $response->assertRedirect()->assertSessionHas('success');
    expect($incident->refresh()->status)->toBe(IncidentStatus::Resolved)
        ->and($incident->title)->toBe('Título preservado')
        ->and($incident->notes()->sole()->is($note))->toBeTrue();
})->with(['open', 'investigating']);

it('reopens only a resolved incident', function () {
    $incident = Incident::factory()->resolved()->create(['logs' => 'trace preservado']);
    $note = IncidentNote::factory()->for($incident)->create();

    $response = $this->delete(route('incidents.resolution.destroy', $incident));

    $response->assertRedirect()->assertSessionHas('success');
    expect($incident->refresh()->status)->toBe(IncidentStatus::Open)
        ->and($incident->logs)->toBe('trace preservado')
        ->and($incident->notes()->sole()->is($note))->toBeTrue();
});

it('rejects invalid status transitions without changing the incident', function (string $routeName, string $method, string $state) {
    $incident = Incident::factory()->{$state}()->create([
        'title' => 'Dados preservados',
        'description' => 'Contexto original',
        'logs' => 'trace original',
    ]);
    $note = IncidentNote::factory()->for($incident)->create();
    $originalAttributes = $incident->getAttributes();

    $response = $this->{$method}(route($routeName, $incident));

    $response->assertRedirect()->assertSessionHas('error');
    expect($incident->refresh()->getAttributes())->toEqual($originalAttributes)
        ->and($incident->notes()->sole()->is($note))->toBeTrue();
})->with([
    'investigate an investigating incident' => ['incidents.investigation.store', 'post', 'investigating'],
    'investigate a resolved incident' => ['incidents.investigation.store', 'post', 'resolved'],
    'resolve a resolved incident' => ['incidents.resolution.store', 'post', 'resolved'],
    'reopen an open incident' => ['incidents.resolution.destroy', 'delete', 'open'],
    'reopen an investigating incident' => ['incidents.resolution.destroy', 'delete', 'investigating'],
]);

it('adds manual notes in every incident status without changing it', function (string $state) {
    $incident = Incident::factory()->{$state}()->create();
    $this->freezeTime();

    $response = $this->post(route('incidents.notes.store', $incident), [
        'content' => 'Banco respondeu depois do timeout configurado.',
    ]);

    $response->assertRedirect()->assertSessionHas('success');
    $this->assertDatabaseHas('incident_notes', [
        'incident_id' => $incident->id,
        'content' => 'Banco respondeu depois do timeout configurado.',
        'created_at' => now(),
    ]);
    expect($incident->refresh()->status->value)->toBe(match ($state) {
        'open' => 'open',
        'investigating' => 'investigating',
        'resolved' => 'resolved',
    });
})->with(['open', 'investigating', 'resolved']);

it('rejects an empty note', function () {
    $incident = Incident::factory()->create();

    $response = $this->post(route('incidents.notes.store', $incident), ['content' => '']);

    $response->assertSessionHasErrors('content');
    $this->assertDatabaseCount('incident_notes', 0);
});

it('shows notes in stable chronological order', function () {
    $incident = Incident::factory()->create();
    $createdAt = now()->subDay();
    $olderFirst = IncidentNote::factory()->for($incident)->create(['created_at' => $createdAt]);
    $olderSecond = IncidentNote::factory()->for($incident)->create(['created_at' => $createdAt]);
    $newest = IncidentNote::factory()->for($incident)->create(['created_at' => now()]);

    $response = $this->get(route('incidents.show', $incident));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Incidents/Show')
        ->where('incident.notes.0.id', $olderFirst->id)
        ->where('incident.notes.1.id', $olderSecond->id)
        ->where('incident.notes.2.id', $newest->id)
        ->missing('incident.notes.0.edit_url')
        ->missing('incident.notes.0.delete_url'));
});
