<?php

use App\Http\Controllers\IncidentAnalysisController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\IncidentConversationController;
use App\Http\Controllers\IncidentInvestigationController;
use App\Http\Controllers\IncidentNoteController;
use App\Http\Controllers\IncidentResolutionController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/projects')->name('home');

Route::resource('projects', ProjectController::class);

Route::resource('incidents', IncidentController::class);

Route::prefix('incidents/{incident}')->name('incidents.')->group(function () {
    Route::post('investigation', [IncidentInvestigationController::class, 'store'])->name('investigation.store');
    Route::post('resolution', [IncidentResolutionController::class, 'store'])->name('resolution.store');
    Route::delete('resolution', [IncidentResolutionController::class, 'destroy'])->name('resolution.destroy');
    Route::post('notes', [IncidentNoteController::class, 'store'])->name('notes.store');
    Route::post('analysis', IncidentAnalysisController::class)->name('analysis');
    Route::post('conversation', IncidentConversationController::class)->name('conversation');
});
