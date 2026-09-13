<?php

namespace App\Ai\Agents;

use App\Ai\Tools\AddIncidentNote;
use App\Ai\Tools\SearchRelatedIncidents;
use App\Models\Incident;
use App\Traits\FormatterHelpers;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider('ollama')]
#[Model('qwen3:4b-instruct')]
class IncidentAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations, FormatterHelpers;

    public function __construct(
        private readonly Incident $incident
    ) {}

    public function instructions(): Stringable|string
    {
        $description = $this->incident->description ?? 'No content';
        $logs = $this->incident->logs ?? 'No content';
        $aiTriageSummary = $this->incident->ai_summary ?? 'No content';
        $aiProbableCauses = $this->converListToString($this->incident->ai_probable_causes);
        $aiRecommendedActions = $this->converListToString($this->incident->ai_recommended_actions);
        $notes = $this->converListToString($this->incident->notes->pluck('content'));

        return <<<PROMPT
        You are an assistant specialized in investigating software incidents.

        Help the user understand and investigate the incident using the available context.

        Clearly distinguish known facts from hypotheses.
        Do not invent information that is not supported by the available context.
        Always respond in Brazilian Portuguese.

        Current incident:

        Title:
        {$this->incident->title}

        Description:
        {$description}

        Logs / Stack trace:
        {$logs}

        Status:
        {$this->incident->status->value}

        Severity:
        {$this->incident->severity->value}

        AI triage summary:
        {$aiTriageSummary}

        AI probable causes:
        {$aiProbableCauses}

        AI recommended actions:
        {$aiRecommendedActions}

        Investigation notes:
        {$notes}

        Incident created at:
        {$this->incident->created_at}

        Incident last update at:
        {$this->incident->updated_at}
        PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new SearchRelatedIncidents($this->incident),
            new AddIncidentNote($this->incident),
        ];
    }
}
