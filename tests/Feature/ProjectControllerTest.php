<?php

use App\Models\Incident;
use App\Models\Project;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the empty project list and project forms', function () {
    $this->get(route('projects.index'))->assertInertia(fn (Assert $page) => $page
        ->component('Projects/Index')
        ->has('projects.data', 0));

    $this->get(route('projects.create'))->assertInertia(fn (Assert $page) => $page
        ->component('Projects/Create'));

    $project = Project::factory()->create();

    $this->get(route('projects.edit', $project))->assertInertia(fn (Assert $page) => $page
        ->component('Projects/Edit')
        ->where('project.id', $project->id));
});

it('renders projects alphabetically with counts and pagination', function () {
    Project::factory()
        ->count(14)
        ->sequence(fn ($sequence) => ['name' => sprintf('Beta %02d', $sequence->index)])
        ->create();
    $zulu = Project::factory()->create(['name' => 'Zulu']);
    $alpha = Project::factory()->create(['name' => 'Alpha']);
    Incident::factory()->count(2)->for($alpha)->create();

    $response = $this->get(route('projects.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Projects/Index')
        ->has('projects.data', 15)
        ->where('projects.data.0.name', 'Alpha')
        ->where('projects.data.0.incidents_count', 2)
        ->where('projects.meta.total', 16)
        ->missing('projects.data.0.incidents'));
    $this->assertModelExists($zulu);
});

it('creates a project and normalizes an empty description', function () {
    $response = $this->post(route('projects.store'), [
        'name' => 'Billing API',
        'description' => '',
    ]);

    $project = Project::query()->sole();
    $response->assertRedirectToRoute('projects.show', $project)
        ->assertSessionHas('success');
    expect($project->description)->toBeNull();
});

it('rejects an empty project name with a visible validation message', function () {
    $response = $this->from(route('projects.create'))->post(route('projects.store'), [
        'name' => '',
        'description' => 'Contexto',
    ]);

    $response->assertRedirect(route('projects.create'))
        ->assertSessionHasErrors('name');
    $this->assertDatabaseCount('projects', 0);
});

it('rejects a project name beyond the storage limit', function () {
    $response = $this->post(route('projects.store'), [
        'name' => str_repeat('a', 256),
    ]);

    $response->assertSessionHasErrors([
        'name' => 'O nome do projeto deve ter no máximo 255 caracteres.',
    ]);
    $this->assertDatabaseCount('projects', 0);
});

it('updates only the intended project fields', function () {
    $project = Project::factory()->create();

    $response = $this->put(route('projects.update', $project), [
        'name' => 'Checkout API',
        'description' => 'Pagamentos do checkout',
        'unexpected' => 'ignored',
    ]);

    $response->assertRedirectToRoute('projects.show', $project)
        ->assertSessionHas('success');
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Checkout API',
        'description' => 'Pagamentos do checkout',
    ]);
});

it('shows only incidents that belong to the project', function () {
    $project = Project::factory()->create();
    $ownIncident = Incident::factory()->for($project)->create(['title' => 'Falha própria']);
    $otherIncident = Incident::factory()->create(['title' => 'Falha externa']);

    $response = $this->get(route('projects.show', $project));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Projects/Show')
        ->where('project.id', $project->id)
        ->has('incidents.data', 1)
        ->where('incidents.data.0.id', $ownIncident->id)
        ->where('incidents.data.0.title', 'Falha própria'));
    $this->assertModelExists($otherIncident);
});

it('deletes a project without incidents', function () {
    $project = Project::factory()->create();

    $response = $this->delete(route('projects.destroy', $project));

    $response->assertRedirectToRoute('projects.index')->assertSessionHas('success');
    $this->assertModelMissing($project);
});

it('refuses to delete a project that has incidents', function () {
    $project = Project::factory()->create();
    Incident::factory()->for($project)->create();

    $response = $this->delete(route('projects.destroy', $project));

    $response->assertRedirect()->assertSessionHas(
        'error',
        'Exclua ou mova os incidentes deste projeto antes de excluí-lo.',
    );
    $this->assertModelExists($project);
});
