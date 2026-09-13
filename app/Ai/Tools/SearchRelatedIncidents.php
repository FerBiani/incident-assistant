<?php

namespace App\Ai\Tools;

use App\Models\Incident;
use App\Traits\FormatterHelpers;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchRelatedIncidents implements Tool
{
    use FormatterHelpers;

    public function __construct(
        private readonly Incident $incident,
    ) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search other incidents that may be related to the current incident
        using technical terms such as error messages, exception names,
        affected components, symptoms, or other relevant keywords.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $terms = data_get($request, 'terms');

        $relatedIncidents = $this->searchRelatedIncidents($terms);

        if ($relatedIncidents->isEmpty()) {
            return 'No related incidents were found.';
        }

        $separator = PHP_EOL . str_repeat('#', 16) . PHP_EOL;

        return $relatedIncidents
            ->map(fn (Incident $incident) => $this->getRelatedIncidentDataFormatted($incident))
            ->implode($separator);
    }

    private function searchRelatedIncidents(array $terms): Collection
    {
        return Incident::query()
            ->whereKeyNot($this->incident->id)
            ->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $query->orWhere(function ($query) use ($term) {
                        $query
                            ->where('title', 'like', "%{$term}%")
                            ->orWhere('description', 'like', "%{$term}%")
                            ->orWhere('logs', 'like', "%{$term}%")
                            ->orWhereHas('notes', function ($query) use ($term) {
                                $query->where('content', 'like', "%{$term}%");
                            });
                    });
                }
            })
            ->limit(5)
            ->get();
    }

    private function getRelatedIncidentDataFormatted(Incident $incident)
    {
        $description = $incident->description ?? 'No content';
        $logs = $incident->logs ?? 'No content';
        $aiTriageSummary = $incident->ai_summary ?? 'No content';
        $aiProbableCauses = $this->converListToString($incident->ai_probable_causes);
        $aiRecommendedActions = $this->converListToString($incident->ai_recommended_actions);
        $notes = $this->converListToString($incident->notes->pluck('content'));

        return <<<INCIDENT
        Incident #{$incident->id}

        Title:
        {$incident->title}

        Description:
        {$description}

        Logs / Stack trace:
        {$logs}

        Status:
        {$incident->status->value}

        Severity:
        {$incident->severity->value}

        AI triage summary:
        {$aiTriageSummary}

        AI probable causes:
        {$aiProbableCauses}

        AI recommended actions:
        {$aiRecommendedActions}

        Investigation notes:
        - {$notes}

        Incident created at:
        {$incident->created_at}

        Incident last update at:
        {$incident->updated_at}
        INCIDENT;
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'terms' => $schema->array()
                ->items(
                    $schema->string()
                )
                ->description(
                    'Search terms likely to appear in related incidents. Prefer exact technical
                    terms from logs, error messages, exception names, or codes when available;
                    otherwise use relevant terms from the incident description,
                    in the language used by the incident data.'
                )
                ->required(),
        ];
    }
}
