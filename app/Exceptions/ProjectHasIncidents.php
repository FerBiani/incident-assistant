<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class ProjectHasIncidents extends RuntimeException implements ShouldntReport
{
    public function __construct()
    {
        parent::__construct('Exclua ou mova os incidentes deste projeto antes de excluí-lo.');
    }
}
