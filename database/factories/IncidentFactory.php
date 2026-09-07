<?php

namespace Database\Factories;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(5),
            'description' => fake()->optional()->paragraph(),
            'logs' => fake()->optional()->text(),
            'severity' => fake()->randomElement(IncidentSeverity::cases()),
            'status' => IncidentStatus::Open,
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => ['status' => IncidentStatus::Open]);
    }

    public function investigating(): static
    {
        return $this->state(fn () => ['status' => IncidentStatus::Investigating]);
    }

    public function resolved(): static
    {
        return $this->state(fn () => ['status' => IncidentStatus::Resolved]);
    }

    public function low(): static
    {
        return $this->state(fn () => ['severity' => IncidentSeverity::Low]);
    }

    public function medium(): static
    {
        return $this->state(fn () => ['severity' => IncidentSeverity::Medium]);
    }

    public function high(): static
    {
        return $this->state(fn () => ['severity' => IncidentSeverity::High]);
    }

    public function critical(): static
    {
        return $this->state(fn () => ['severity' => IncidentSeverity::Critical]);
    }
}
