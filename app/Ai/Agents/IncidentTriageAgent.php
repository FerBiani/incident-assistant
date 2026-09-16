<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[Temperature(0.2)]
#[Timeout(120)]
class IncidentTriageAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<PROMPT
            You are an assistant specialized in triaging software incidents.

            Analyze the incident information provided by the user.

            Your goal is to:
            - summarize the problem;
            - identify possible causes;
            - suggest investigation steps;
            - suggest a severity level.

            Important:
            - Do not invent information that is not present in the incident.
            - Clearly distinguish facts from hypotheses.
            - Return all human-readable content in Brazilian Portuguese.
            - Keep structured field names and enum values unchanged.
            PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema->string()->required(),

            'severity' => $schema->string()
                ->enum(['low', 'medium', 'high', 'critical'])
                ->required(),

            'probable_causes' => $schema->array()
                ->items($schema->string())
                ->required(),

            'recommended_actions' => $schema->array()
                ->items($schema->string())
                ->required(),
        ];
    }

    public function provider(): string
    {
        return config('ai.default_provider');
    }

    public function model(): string
    {
        return config('ai.default_model');
    }
}
