<?php

namespace App\Http\Resources;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Incident */
class IncidentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'title' => $this->title,
            'description' => $this->description,
            'logs' => $this->logs,
            'severity' => $this->severity->value,
            'severity_label' => $this->severity->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'analysis' => [
                'status' => $this->ai_analysis_status->value,
                'summary' => $this->ai_summary,
                'suggested_severity' => $this->ai_severity?->value,
                'suggested_severity_label' => $this->ai_severity?->label(),
                'probable_causes' => $this->ai_probable_causes ?? [],
                'recommended_actions' => $this->ai_recommended_actions ?? [],
                'analyzed_at' => $this->ai_analyzed_at?->toISOString(),
            ],
            'notes' => IncidentNoteResource::collection($this->whenLoaded('notes')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
