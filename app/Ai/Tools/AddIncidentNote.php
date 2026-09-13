<?php

namespace App\Ai\Tools;

use App\Models\Incident;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class AddIncidentNote implements Tool
{
    public function __construct(
        private readonly Incident $incident,
    ) {}

    public function description(): Stringable|string
    {
        return 'Add a note to the current incident with relevant information
        discovered during the investigation.';
    }

    public function handle(Request $request): Stringable|string
    {
        $content = data_get($request, 'content');

        $this->incident->notes()->create([
            'content' => $content,
            'source' => 'AI',
        ]);

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
}
