<?php

use App\Actions\AddIncidentNote;
use App\Ai\Agents\IncidentAgent;
use App\Enums\IncidentNoteSource;
use App\Models\Incident;
use Laravel\Ai\Responses\Data\ToolCall;
use Laravel\Ai\Tools\Request;

it('streams and preserves the first conversation for the incident', function () {
    $incident = Incident::factory()->create();
    IncidentAgent::fake(['Primeiro delta segundo delta']);

    $response = $this->postJson(route('incidents.conversation', $incident), [
        'message' => 'Como investigar?',
    ]);
    $response->assertOk()->assertHeader('content-type', 'text/event-stream; charset=UTF-8');
    $content = $response->streamedContent();

    expect($content)->toContain('"type":"text_delta"')->toContain('data: [DONE]');

    $conversation = $incident->conversations()->sole();
    expect($conversation->participant->is($incident))->toBeTrue()
        ->and($conversation->messages()->orderBy('created_at')->pluck('role')->all())
        ->toBe(['user', 'assistant'])
        ->and($conversation->messages()->first()->content)->toBe('Como investigar?');
});

it('continues only the latest conversation belonging to the current incident', function () {
    $firstIncident = Incident::factory()->create();
    $secondIncident = Incident::factory()->create();
    IncidentAgent::fake(['Resposta um', 'Resposta dois', 'Resposta isolada']);

    $this->postJson(route('incidents.conversation', $firstIncident), ['message' => 'Primeira'])->streamedContent();
    $conversationId = $firstIncident->conversations()->sole()->id;
    $this->postJson(route('incidents.conversation', $firstIncident), ['message' => 'Segunda'])->streamedContent();
    $this->postJson(route('incidents.conversation', $secondIncident), ['message' => 'Outra'])->streamedContent();

    expect($firstIncident->conversations()->count())->toBe(1)
        ->and($firstIncident->conversations()->sole()->id)->toBe($conversationId)
        ->and($firstIncident->conversations()->sole()->messages)->toHaveCount(4)
        ->and($secondIncident->conversations()->sole()->id)->not->toBe($conversationId)
        ->and($secondIncident->conversations()->sole()->messages)->toHaveCount(2);
});

it('validates mutually exclusive conversation payloads', function (array $payload, array $errors) {
    $incident = Incident::factory()->create();

    $this->postJson(route('incidents.conversation', $incident), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'empty payload' => [[], ['message', 'decisions']],
    'blank message' => [['message' => '   '], ['message']],
    'both formats' => [[
        'message' => 'Continue',
        'decisions' => ['call-1' => ['action' => 'approve']],
    ], ['message', 'decisions']],
]);

it('does not create an AI note before approval and accepts the complete decision', function () {
    $incident = Incident::factory()->create();
    IncidentAgent::fake([
        new ToolCall('call-1', 'AddIncidentNote', ['content' => 'Cache saturado.']),
        'A nota foi registrada.',
    ]);

    $pendingContent = $this->postJson(route('incidents.conversation', $incident), [
        'message' => 'Registre essa descoberta.',
    ])->streamedContent();

    expect($pendingContent)->toContain('"type":"tool_approval_request"')
        ->and($incident->notes()->count())->toBe(0);

    $this->postJson(route('incidents.conversation', $incident), [
        'message' => 'Uma nova mensagem indevida',
    ])->assertUnprocessable()->assertJsonValidationErrors('message');

    $approvedContent = $this->postJson(route('incidents.conversation', $incident), [
        'decisions' => ['call-1' => ['action' => 'approve']],
    ])->streamedContent();

    expect($approvedContent)->toContain('data: [DONE]')
        ->and($incident->notes()->count())->toBe(0);
});

it('creates an approved note through the tool action with AI origin', function () {
    $incident = Incident::factory()->create();
    $tool = new App\Ai\Tools\AddIncidentNote($incident, app(AddIncidentNote::class));

    $tool->handle(new Request(['content' => 'Cache saturado.']));

    expect($incident->notes()->sole()->content)->toBe('Cache saturado.')
        ->and($incident->notes()->sole()->source)->toBe(IncidentNoteSource::Ai);
});

it('rejects a pending note without executing it', function () {
    $incident = Incident::factory()->create();
    IncidentAgent::fake([
        new ToolCall('call-2', 'AddIncidentNote', ['content' => 'Hipótese não confirmada.']),
        'A recusa foi registrada.',
    ]);

    $this->postJson(route('incidents.conversation', $incident), ['message' => 'Crie a nota.'])
        ->streamedContent();
    $this->postJson(route('incidents.conversation', $incident), [
        'decisions' => ['call-2' => ['action' => 'reject']],
    ])->streamedContent();

    expect($incident->notes()->count())->toBe(0);
});

it('rejects unknown or incomplete approval decisions', function () {
    $incident = Incident::factory()->create();
    IncidentAgent::fake([
        new ToolCall('call-3', 'AddIncidentNote', ['content' => 'Nota pendente.']),
    ]);
    $this->postJson(route('incidents.conversation', $incident), ['message' => 'Crie uma nota.'])
        ->streamedContent();

    $this->postJson(route('incidents.conversation', $incident), [
        'decisions' => ['unknown' => ['action' => 'approve']],
    ])->assertUnprocessable()->assertJsonValidationErrors('decisions');

    $pausedMessage = $incident->conversations()->sole()->messages()->where('role', 'assistant')->sole();
    $pausedMessage->update([
        'tool_calls' => [
            ['id' => 'call-3', 'name' => 'AddIncidentNote', 'arguments' => ['content' => 'Nota um']],
            ['id' => 'call-4', 'name' => 'AddIncidentNote', 'arguments' => ['content' => 'Nota dois']],
        ],
        'approval_state' => ['pending' => ['call-3' => 'Pendente', 'call-4' => 'Pendente']],
    ]);

    $this->postJson(route('incidents.conversation', $incident), [
        'decisions' => ['call-3' => ['action' => 'approve']],
    ])->assertUnprocessable()->assertJsonValidationErrors('decisions');

    $pausedMessage->update(['approval_state' => ['pending' => []]]);

    $this->postJson(route('incidents.conversation', $incident), [
        'decisions' => ['call-3' => ['action' => 'approve']],
    ])->assertUnprocessable()->assertJsonValidationErrors('decisions');
});
