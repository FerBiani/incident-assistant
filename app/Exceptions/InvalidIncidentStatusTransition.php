<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class InvalidIncidentStatusTransition extends RuntimeException implements ShouldntReport
{
    public function __construct()
    {
        parent::__construct('Esta alteração de status não é válida para o estado atual do incidente.');
    }
}
