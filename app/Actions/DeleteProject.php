<?php

namespace App\Actions;

use App\Exceptions\ProjectHasIncidents;
use App\Models\Project;

class DeleteProject
{
    public function handle(Project $project): void
    {
        if ($project->incidents()->exists()) {
            throw new ProjectHasIncidents;
        }

        $project->delete();
    }
}
