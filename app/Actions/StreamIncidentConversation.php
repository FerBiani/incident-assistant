<?php

namespace App\Actions;

use App\Ai\Agents\IncidentAgent;
use App\Models\Incident;
use Illuminate\Validation\ValidationException;
use Laravel\Ai\Approvals\Decision;
use Laravel\Ai\Approvals\Decisions;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;
use Laravel\Ai\Responses\StreamableAgentResponse;

class StreamIncidentConversation
{
    /** @param array<string, array{action: 'approve'|'reject'}>|null $decisions */
    public function handle(Incident $incident, ?string $message, ?array $decisions): StreamableAgentResponse
    {
        $conversation = $incident->conversations()
            ->latest('updated_at')
            ->latest('id')
            ->first();
        $pendingIds = $this->pendingApprovalIds($conversation);

        if ($message !== null && $pendingIds !== []) {
            throw ValidationException::withMessages([
                'message' => 'Decida todas as ações pendentes antes de enviar outra mensagem.',
            ]);
        }

        if ($decisions !== null) {
            $decisionIds = array_keys($decisions);
            sort($decisionIds);
            sort($pendingIds);

            if ($conversation === null || $decisionIds !== $pendingIds) {
                throw ValidationException::withMessages([
                    'decisions' => 'Decida uma vez todas e somente as ações que ainda estão pendentes.',
                ]);
            }
        }

        $agent = IncidentAgent::make(incident: $incident->loadMissing('notes'));

        if ($conversation === null) {
            $agent->forParticipant($incident);
        } else {
            $agent->continue($conversation->id, as: $incident);
        }

        $prompt = $decisions === null
            ? trim((string) $message)
            : $this->toSdkDecisions($decisions);

        return $agent->stream($prompt);
    }

    /** @return list<string> */
    private function pendingApprovalIds(?Conversation $conversation): array
    {
        if ($conversation === null) {
            return [];
        }

        return $conversation->messages()
            ->whereNotNull('approval_state')
            ->latest('created_at')
            ->latest('id')
            ->get()
            ->map(fn (ConversationMessage $message): array => array_keys($message->approval_state['pending'] ?? []))
            ->first(fn (array $ids): bool => $ids !== [], []);
    }

    /** @param array<string, array{action: 'approve'|'reject'}> $decisions */
    private function toSdkDecisions(array $decisions): Decisions
    {
        return Decisions::from(array_map(
            fn (array $decision): Decision => $decision['action'] === 'approve'
                ? Decision::approve()
                : Decision::reject('O usuário rejeitou esta ação.'),
            $decisions,
        ));
    }
}
