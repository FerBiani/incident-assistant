<?php

namespace App\Actions;

use App\Models\Project;

class CreateProject
{
    /** @param array{name: string, description: string|null} $attributes */
    public function handle(array $attributes): Project
    {
        return Project::query()->create($attributes);
    }
}
