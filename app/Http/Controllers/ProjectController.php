<?php

namespace App\Http\Controllers;

use App\Actions\CreateProject;
use App\Actions\DeleteProject;
use App\Actions\UpdateProject;
use App\Exceptions\ProjectHasIncidents;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\IncidentSummaryResource;
use App\Http\Resources\ProjectResource;
use App\Models\Incident;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        $projects = Project::query()
            ->select(['id', 'name', 'description', 'created_at', 'updated_at'])
            ->withCount('incidents')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15);

        return Inertia::render('Projects/Index', [
            'projects' => ProjectResource::collection($projects),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Projects/Create');
    }

    public function store(StoreProjectRequest $request, CreateProject $createProject): RedirectResponse
    {
        /** @var array{name: string, description: string|null} $attributes */
        $attributes = $request->validated();
        $project = $createProject->handle($attributes);

        return to_route('projects.show', $project)
            ->with('success', 'Projeto criado com sucesso.');
    }

    public function show(Project $project): Response
    {
        $incidents = Incident::query()
            ->select(['id', 'project_id', 'title', 'severity', 'status', 'created_at', 'updated_at'])
            ->whereBelongsTo($project)
            ->with('project:id,name,description')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return Inertia::render('Projects/Show', [
            'project' => new ProjectResource($project->loadCount('incidents')),
            'incidents' => IncidentSummaryResource::collection($incidents),
        ]);
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('Projects/Edit', [
            'project' => new ProjectResource($project),
        ]);
    }

    public function update(
        UpdateProjectRequest $request,
        Project $project,
        UpdateProject $updateProject,
    ): RedirectResponse {
        /** @var array{name: string, description: string|null} $attributes */
        $attributes = $request->validated();
        $updateProject->handle($project, $attributes);

        return to_route('projects.show', $project)
            ->with('success', 'Projeto atualizado com sucesso.');
    }

    public function destroy(Project $project, DeleteProject $deleteProject): RedirectResponse
    {
        try {
            $deleteProject->handle($project);
        } catch (ProjectHasIncidents $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return to_route('projects.index')
            ->with('success', 'Projeto excluído com sucesso.');
    }
}
