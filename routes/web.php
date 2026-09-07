<?php

use App\Http\Controllers\IncidentController;
use App\Http\Controllers\IncidentInvestigationController;
use App\Http\Controllers\IncidentNoteController;
use App\Http\Controllers\IncidentResolutionController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/projects')->name('home');

Route::resource('projects', ProjectController::class);
Route::resource('incidents', IncidentController::class);

Route::post('incidents/{incident}/investigation', [IncidentInvestigationController::class, 'store'])
    ->name('incidents.investigation.store');
Route::post('incidents/{incident}/resolution', [IncidentResolutionController::class, 'store'])
    ->name('incidents.resolution.store');
Route::delete('incidents/{incident}/resolution', [IncidentResolutionController::class, 'destroy'])
    ->name('incidents.resolution.destroy');
Route::post('incidents/{incident}/notes', [IncidentNoteController::class, 'store'])
    ->name('incidents.notes.store');
