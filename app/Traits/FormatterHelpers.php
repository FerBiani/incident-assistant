<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait FormatterHelpers
{
    private function converListToString(Collection|array|null $items): string
    {
        return collect($items)
            ->map(fn ($item) => "- {$item}")
            ->join(PHP_EOL) ?: 'No content';
    }
}
