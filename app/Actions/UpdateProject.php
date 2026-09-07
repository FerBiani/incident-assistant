<?php

namespace App\Actions;

use App\Models\Project;

class UpdateProject
{
    /** @param array{name: string, description: string|null} $attributes */
    public function handle(Project $project, array $attributes): Project
    {
        $project->update($attributes);

        return $project->refresh();
    }
}
