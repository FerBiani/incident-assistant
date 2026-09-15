<?php

namespace App\Ai\Tools;

use App\Actions\AddIncidentNote as AddIncidentNoteAction;
use App\Enums\IncidentNoteSource;
use App\Models\Incident;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class AddIncidentNote implements Approvable, Tool
{
    use InteractsWithApprovals;

    public function __construct(
        private readonly Incident $incident,
        private readonly AddIncidentNoteAction $addIncidentNote,
    ) {}

    public function description(): Stringable|string
    {
        return 'Add a note to the current incident with relevant information
        discovered during the investigation.';
    }

    public function handle(Request $request): Stringable|string
    {
        $content = data_get($request, 'content');

        $this->addIncidentNote->handle($this->incident, $content, IncidentNoteSource::Ai);

        return 'Nota criada com sucesso!';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'content' => $schema->string()
                ->description('
                    Relevant information discovered during the investigation that
                    should be recorded as an incident note.'
                )
                ->required(),
        ];
    }

    protected function needsApproval(): Approval|bool
    {
        return Approval::required(
            'Uma nota será adicionada ao histórico de investigação do incidente.'
        );
    }
}
