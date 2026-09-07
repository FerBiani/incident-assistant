<?php

namespace App\Http\Controllers;

use App\Actions\AddIncidentNote;
use App\Http\Requests\StoreIncidentNoteRequest;
use App\Models\Incident;
use Illuminate\Http\RedirectResponse;

class IncidentNoteController extends Controller
{
    public function store(
        StoreIncidentNoteRequest $request,
        Incident $incident,
        AddIncidentNote $addIncidentNote,
    ): RedirectResponse {
        $addIncidentNote->handle($incident, $request->string('content')->toString());

        return back()->with('success', 'Nota adicionada ao histórico.');
    }
}
