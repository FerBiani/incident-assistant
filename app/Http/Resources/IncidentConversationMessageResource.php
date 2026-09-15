<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Ai\Models\ConversationMessage;

/** @mixin ConversationMessage */
class IncidentConversationMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pending = $this->approval_state['pending'] ?? [];
        $results = collect($this->tool_results)->keyBy('id');

        $approvals = collect($this->tool_calls)
            ->filter(fn (array $call): bool => ($call['name'] ?? null) === 'AddIncidentNote')
            ->map(function (array $call) use ($pending, $results): array {
                $id = (string) $call['id'];
                $result = $results->get($id);

                return [
                    'id' => $id,
                    'tool' => 'AddIncidentNote',
                    'description' => 'Adicionar nota ao histórico de investigação',
                    'arguments' => $call['arguments'] ?? [],
                    'result' => $result['result'] ?? null,
                    'status' => match (true) {
                        array_key_exists($id, $pending) => 'pending',
                        (bool) ($result['denied'] ?? false) => 'rejected',
                        (bool) ($result['failed'] ?? false) => 'failed',
                        $result !== null => 'approved',
                        default => 'pending',
                    },
                ];
            })
            ->values();

        return [
            'id' => $this->id,
            'role' => $this->role,
            'content' => $this->content,
            'created_at' => $this->created_at?->toISOString(),
            'approvals' => $approvals,
        ];
    }
}
