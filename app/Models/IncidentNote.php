<?php

namespace App\Models;

use App\Enums\IncidentNoteSource;
use Database\Factories\IncidentNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $incident_id
 * @property string $content
 * @property IncidentNoteSource $source
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['incident_id', 'content', 'source'])]
class IncidentNote extends Model
{
    /** @use HasFactory<IncidentNoteFactory> */
    use HasFactory;

    /** @return BelongsTo<Incident, $this> */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    protected function casts(): array
    {
        return [
            'source' => IncidentNoteSource::class,
        ];
    }
}
