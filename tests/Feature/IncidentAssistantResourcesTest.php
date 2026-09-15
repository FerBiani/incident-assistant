<?php

use App\Ai\Agents\IncidentAgent;
use App\Enums\IncidentAnalysisStatus;
use App\Enums\IncidentNoteSource;
use App\Enums\IncidentSeverity;
use App\Models\Incident;
use App\Models\IncidentNote;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;

it('exposes the complete analysis and notes in stable chronological order', function () {
    $incident = Incident::factory()->create([
        'ai_analysis_status' => IncidentAnalysisStatus::Completed,
        'ai_summary' => 'O worker perdeu conexão com o banco.',
        'ai_severity' => IncidentSeverity::High,
        'ai_probable_causes' => ['Pool esgotado'],
        'ai_recommended_actions' => ['Revisar conexões'],
        'ai_analyzed_at' => now(),
    ]);
    $createdAt = now()->subHour();
    $first = IncidentNote::factory()->for($incident)->create([
        'content' => 'Nota manual',
        'source' => IncidentNoteSource::User,
        'created_at' => $createdAt,
    ]);
    $second = IncidentNote::factory()->for($incident)->create([
        'content' => 'Nota da IA',
        'source' => IncidentNoteSource::Ai,
        'created_at' => $createdAt,
    ]);

    $this->get(route('incidents.show', $incident))->assertInertia(fn (Assert $page) => $page
        ->where('incident.analysis.status', 'completed')
        ->where('incident.analysis.summary', 'O worker perdeu conexão com o banco.')
        ->where('incident.analysis.suggested_severity', 'high')
        ->where('incident.analysis.suggested_severity_label', 'Alta')
        ->where('incident.analysis.probable_causes', ['Pool esgotado'])
        ->where('incident.analysis.recommended_actions', ['Revisar conexões'])
        ->where('incident.notes.0.id', $first->id)
        ->where('incident.notes.0.source_label', 'Usuário')
        ->where('incident.notes.1.id', $second->id)
        ->where('incident.notes.1.source_label', 'IA')
        ->where('conversation', null));
});

it('exposes every analysis status even without analysis content', function (IncidentAnalysisStatus $status) {
    $incident = Incident::factory()->create(['ai_analysis_status' => $status]);

    $this->get(route('incidents.show', $incident))->assertInertia(fn (Assert $page) => $page
        ->where('incident.analysis.status', $status->value)
        ->where('incident.analysis.summary', null)
        ->where('incident.analysis.probable_causes', [])
        ->where('incident.analysis.recommended_actions', []));
})->with(IncidentAnalysisStatus::cases());

it('serializes conversation messages and only approvable note actions', function () {
    $incident = Incident::factory()->create();
    $conversation = createConversationFor($incident);
    createConversationMessage($conversation, [
        'role' => 'user',
        'content' => 'Investigue o incidente.',
        'created_at' => now()->subSecond(),
    ]);
    $assistant = createConversationMessage($conversation, [
        'role' => 'assistant',
        'content' => 'Preciso registrar uma descoberta.',
        'tool_calls' => [
            ['id' => 'note-1', 'name' => 'AddIncidentNote', 'arguments' => ['content' => 'Conexão esgotada']],
            ['id' => 'search-1', 'name' => 'SearchRelatedIncidents', 'arguments' => ['terms' => ['timeout']]],
        ],
        'approval_state' => ['pending' => ['note-1' => 'Confirme a criação da nota.']],
    ]);

    $this->get(route('incidents.show', $incident))->assertInertia(fn (Assert $page) => $page
        ->where('conversation.id', $conversation->id)
        ->where('conversation.messages.0.role', 'user')
        ->where('conversation.messages.1.id', $assistant->id)
        ->has('conversation.messages.1.approvals', 1)
        ->where('conversation.messages.1.approvals.0.id', 'note-1')
        ->where('conversation.messages.1.approvals.0.status', 'pending'));
});

it('serializes resolved approval states', function (array $result, string $status) {
    $incident = Incident::factory()->create();
    $conversation = createConversationFor($incident);
    createConversationMessage($conversation, [
        'tool_calls' => [['id' => 'note-1', 'name' => 'AddIncidentNote', 'arguments' => ['content' => 'Nota']]],
        'tool_results' => [[
            'id' => 'note-1',
            'name' => 'AddIncidentNote',
            'arguments' => ['content' => 'Nota'],
            'result' => $result['value'],
            ...$result['flags'],
        ]],
        'approval_state' => ['pending' => []],
    ]);

    $this->get(route('incidents.show', $incident))->assertInertia(fn (Assert $page) => $page
        ->where('conversation.messages.0.approvals.0.status', $status));
})->with([
    'approved' => [['value' => 'Nota criada.', 'flags' => []], 'approved'],
    'rejected' => [['value' => 'Ação rejeitada.', 'flags' => ['denied' => true]], 'rejected'],
    'failed' => [['value' => 'Falha ao criar.', 'flags' => ['failed' => true]], 'failed'],
]);

it('deletes only the conversations and messages belonging to the deleted incident', function () {
    $deletedIncident = Incident::factory()->create();
    $keptIncident = Incident::factory()->create();
    $deletedConversation = createConversationFor($deletedIncident);
    $keptConversation = createConversationFor($keptIncident);
    $deletedMessage = createConversationMessage($deletedConversation);
    $keptMessage = createConversationMessage($keptConversation);

    $this->delete(route('incidents.destroy', $deletedIncident))->assertRedirect();

    expect(Conversation::query()->find($deletedConversation->id))->toBeNull()
        ->and(ConversationMessage::query()->find($deletedMessage->id))->toBeNull()
        ->and(Conversation::query()->find($keptConversation->id))->not->toBeNull()
        ->and(ConversationMessage::query()->find($keptMessage->id))->not->toBeNull();
});

function createConversationFor(Incident $incident): Conversation
{
    return Conversation::query()->create([
        'id' => (string) Str::uuid(),
        'participant_type' => $incident->getMorphClass(),
        'participant_id' => $incident->getKey(),
        'title' => 'Investigação',
    ]);
}

/** @param array<string, mixed> $overrides */
function createConversationMessage(Conversation $conversation, array $overrides = []): ConversationMessage
{
    return $conversation->messages()->create([
        'id' => (string) Str::uuid(),
        'participant_type' => $conversation->participant_type,
        'participant_id' => $conversation->participant_id,
        'agent' => IncidentAgent::class,
        'role' => 'assistant',
        'content' => '',
        'attachments' => [],
        'tool_calls' => [],
        'tool_results' => [],
        'usage' => [],
        'meta' => [],
        'approval_state' => null,
        ...$overrides,
    ]);
}
