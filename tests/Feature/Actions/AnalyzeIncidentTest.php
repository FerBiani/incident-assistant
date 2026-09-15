<?php

use App\Actions\AnalyzeIncident;
use App\Ai\Agents\IncidentTriageAgent;
use App\Enums\IncidentSeverity;
use App\Models\Incident;
use App\Models\IncidentNote;

it('analyzes an incident using the triage agent', function () {
    $incident = createIncidentWithNotes();

    IncidentTriageAgent::fake([
        [
            'summary' => 'Falha de comunicação com o gateway.',
            'severity' => 'high',
            'probable_causes' => [
                'Timeout no gateway',
            ],
            'recommended_actions' => [
                'Verificar disponibilidade do gateway',
            ],
        ],
    ]);

    (new AnalyzeIncident)->handle($incident);

    $incident->refresh();

    $promptText = getActionPrompt($incident);

    IncidentTriageAgent::assertPrompted($promptText);

    $this->assertSame(
        'Falha de comunicação com o gateway.',
        $incident->ai_summary,
    );

    $this->assertSame(
        IncidentSeverity::High,
        $incident->ai_severity,
    );

    $this->assertSame(
        ['Timeout no gateway'],
        $incident->ai_probable_causes,
    );

    $this->assertSame(
        ['Verificar disponibilidade do gateway'],
        $incident->ai_recommended_actions,
    );
});

function createIncidentWithNotes(): Incident
{
    return Incident::factory()
        ->has(IncidentNote::factory()->count(3), 'notes')
        ->create([
            'title' => 'Checkout retornando erro 500',
            'description' => 'Usuários não conseguem finalizar compras.',
            'logs' => 'PaymentGatewayTimeoutException',
        ]);
}

function getActionPrompt(Incident $incident): string
{
    return <<<PROMPT
    Title: {$incident->title}

    Description:
    {$incident->description}

    Logs / Stack trace:
    {$incident->logs}

    Analisys notes:
    {$incident->notes->pluck('content')->join('; ')}
    PROMPT;
}
