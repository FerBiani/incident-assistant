<?php

namespace App\Http\Controllers;

use App\Actions\CreateIncident;
use App\Actions\DeleteIncident;
use App\Actions\UpdateIncident;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Http\Requests\FilterIncidentsRequest;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Http\Resources\IncidentSummaryResource;
use App\Models\Incident;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class IncidentController extends Controller
{
    public function index(FilterIncidentsRequest $request): Response
    {
        $filters = $request->filters();
        $incidents = Incident::query()
            ->select(['id', 'project_id', 'title', 'severity', 'status', 'created_at', 'updated_at'])
            ->with('project:id,name,description')
            ->when($filters['project_id'], fn ($query, $projectId) => $query->where('project_id', $projectId))
            ->when($filters['severity'], fn ($query, $severity) => $query->where('severity', $severity))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Incidents/Index', [
            'incidents' => IncidentSummaryResource::collection($incidents),
            'projects' => $this->projectOptions(),
            'severities' => $this->severityOptions(),
            'statuses' => $this->statusOptions(),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        $projects = $this->projectOptions();

        return Inertia::render('Incidents/Create', [
            'projects' => $projects,
            'severities' => $this->severityOptions(),
            'canCreate' => $projects !== [],
        ]);
    }

    public function store(StoreIncidentRequest $request, CreateIncident $createIncident): RedirectResponse
    {
        /** @var array{project_id: int, title: string, description: string|null, logs: string|null, severity: string} $attributes */
        $attributes = $request->validated();
        $incident = $createIncident->handle($attributes);

        return to_route('incidents.show', $incident)
            ->with('success', 'Incidente criado com sucesso.');
    }

    public function show(Incident $incident): Response
    {
        $incident->load([
            'project:id,name,description',
            'notes' => fn ($query) => $query->orderBy('created_at', 'desc'),
        ]);

        return Inertia::render('Incidents/Show', [
            'incident' => new IncidentResource($incident),
        ]);
    }

    public function edit(Incident $incident): Response
    {
        return Inertia::render('Incidents/Edit', [
            'incident' => new IncidentResource($incident->load('project:id,name,description')),
            'projects' => $this->projectOptions(),
            'severities' => $this->severityOptions(),
        ]);
    }

    public function update(
        UpdateIncidentRequest $request,
        Incident $incident,
        UpdateIncident $updateIncident,
    ): RedirectResponse {
        /** @var array{project_id: int, title: string, description: string|null, logs: string|null, severity: string} $attributes */
        $attributes = $request->validated();
        $updateIncident->handle($incident, $attributes);

        return to_route('incidents.show', $incident)
            ->with('success', 'Incidente atualizado com sucesso.');
    }

    public function destroy(Incident $incident, DeleteIncident $deleteIncident): RedirectResponse
    {
        $deleteIncident->handle($incident);

        return to_route('incidents.index')
            ->with('success', 'Incidente excluído com sucesso.');
    }

    /** @return list<array{value: int, label: string}> */
    private function projectOptions(): array
    {
        return array_values(Project::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->map(fn (Project $project): array => [
                'value' => $project->id,
                'label' => $project->name,
            ])
            ->all());
    }

    /** @return list<array{value: string, label: string}> */
    private function severityOptions(): array
    {
        return array_map(
            fn (IncidentSeverity $severity): array => [
                'value' => $severity->value,
                'label' => $severity->label(),
            ],
            IncidentSeverity::cases(),
        );
    }

    /** @return list<array{value: string, label: string}> */
    private function statusOptions(): array
    {
        return array_map(
            fn (IncidentStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            IncidentStatus::cases(),
        );
    }
}
