<?php

namespace Database\Factories;

use App\Enums\IncidentNoteSource;
use App\Models\Incident;
use App\Models\IncidentNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncidentNote>
 */
class IncidentNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'incident_id' => Incident::factory(),
            'content' => fake()->paragraph(),
            'source' => IncidentNoteSource::User,
        ];
    }
}
