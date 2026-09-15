<?php

namespace App\Http\Controllers;

use App\Actions\StreamIncidentConversation;
use App\Http\Requests\StreamIncidentConversationRequest;
use App\Models\Incident;
use Laravel\Ai\Responses\StreamableAgentResponse;

class IncidentConversationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        StreamIncidentConversationRequest $request,
        Incident $incident,
        StreamIncidentConversation $streamIncidentConversation,
    ): StreamableAgentResponse {
        /** @var array<string, array{action: 'approve'|'reject'}>|null $decisions */
        $decisions = $request->validated('decisions');

        return $streamIncidentConversation->handle(
            $incident,
            $request->validated('message'),
            $decisions,
        );
    }
}
