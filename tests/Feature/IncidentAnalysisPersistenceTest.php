<?php

use App\Enums\IncidentAnalysisStatus;
use App\Enums\IncidentNoteSource;
use App\Http\Resources\IncidentNoteResource;
use App\Models\Incident;
use App\Models\IncidentNote;
use Illuminate\Http\Request;

it('casts every analysis status and defaults new incidents to not started', function () {
    $incident = Incident::factory()->create();

    expect($incident->ai_analysis_status)->toBe(IncidentAnalysisStatus::NotStarted);

    foreach (IncidentAnalysisStatus::cases() as $status) {
        $incident->update(['ai_analysis_status' => $status]);

        expect($incident->refresh()->ai_analysis_status)->toBe($status);
    }
});

it('casts note sources and serializes their textual labels', function (IncidentNoteSource $source, string $label) {
    $note = IncidentNote::factory()->create(['source' => $source]);
    $serialized = (new IncidentNoteResource($note))->toArray(Request::create('/'));

    expect($note->source)->toBe($source)
        ->and($serialized['source'])->toBe($source->value)
        ->and($serialized['source_label'])->toBe($label);
})->with([
    'user note' => [IncidentNoteSource::User, 'Usuário'],
    'AI note' => [IncidentNoteSource::Ai, 'IA'],
]);

it('defaults notes to the user source', function () {
    $note = IncidentNote::factory()->create();

    expect($note->source)->toBe(IncidentNoteSource::User);
});
