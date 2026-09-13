<?php

namespace App\Models;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property string $title
 * @property string|null $description
 * @property string|null $logs
 * @property IncidentSeverity $severity
 * @property IncidentStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'project_id',
    'title',
    'description',
    'logs',
    'severity',
    'status',
    'ai_severity',
    'ai_probable_causes',
    'ai_recommended_actions',
    'ai_summary',
    'ai_analyzed_at',
])]
class Incident extends Model
{
    /** @use HasFactory<IncidentFactory> */
    use HasFactory;

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<IncidentNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(IncidentNote::class);
    }

    /** @return array<string, class-string> */
    protected function casts(): array
    {
        return [
            'severity' => IncidentSeverity::class,
            'status' => IncidentStatus::class,
            'ai_probable_causes' => 'array',
            'ai_recommended_actions' => 'array',
            'ai_analyzed_at' => 'datetime',
        ];
    }
}
