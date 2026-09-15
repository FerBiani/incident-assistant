<?php

use App\Actions\AddIncidentNote as AddIncidentNoteAction;
use App\Ai\Agents\IncidentAgent;
use App\Ai\Tools\AddIncidentNote;
use App\Ai\Tools\SearchRelatedIncidents;
use App\Models\Incident;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Tools\Request;

it('offers exactly the note and related incident tools with approval only for notes', function () {
    $incident = Incident::factory()->create();
    $tools = [...IncidentAgent::make(incident: $incident)->tools()];

    expect($tools)->toHaveCount(2)
        ->and($tools[0])->toBeInstanceOf(SearchRelatedIncidents::class)
        ->and($tools[0])->not->toBeInstanceOf(Approvable::class)
        ->and($tools[1])->toBeInstanceOf(AddIncidentNote::class)
        ->and($tools[1])->toBeInstanceOf(Approvable::class);
});

it('searches matching related incidents without returning the current incident or changing data', function () {
    $current = Incident::factory()->create([
        'title' => 'Timeout no gateway',
        'logs' => 'PaymentGatewayTimeout',
    ]);
    $related = Incident::factory()->create([
        'title' => 'Falha anterior no pagamento',
        'logs' => 'PaymentGatewayTimeout',
    ]);
    $unrelated = Incident::factory()->create(['title' => 'CSS desalinhado']);
    $before = Incident::query()->orderBy('id')->get()->map->getAttributes();

    $result = (new SearchRelatedIncidents($current))->handle(
        new Request(['terms' => ['PaymentGatewayTimeout']]),
    );

    expect((string) $result)->toContain("Incident #{$related->id}")
        ->not->toContain("Incident #{$current->id}")
        ->not->toContain("Incident #{$unrelated->id}")
        ->and(Incident::query()->orderBy('id')->get()->map->getAttributes())->toEqual($before);
});

it('writes assistant notes only to the incident provided to the tool', function () {
    $current = Incident::factory()->create();
    $other = Incident::factory()->create();
    $tool = new AddIncidentNote($current, app(AddIncidentNoteAction::class));

    $tool->handle(new Request(['content' => 'Descoberta confirmada.']));

    expect($current->notes()->sole()->content)->toBe('Descoberta confirmada.')
        ->and($other->notes()->count())->toBe(0);
});
