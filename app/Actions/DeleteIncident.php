<?php

namespace App\Actions;

use App\Models\Incident;
use Illuminate\Support\Facades\DB;

class DeleteIncident
{
    public function handle(Incident $incident): void
    {
        DB::transaction(function () use ($incident): void {
            $incident->conversations()->each(function ($conversation): void {
                $conversation->messages()->delete();
                $conversation->delete();
            });

            $incident->delete();
        });
    }
}
