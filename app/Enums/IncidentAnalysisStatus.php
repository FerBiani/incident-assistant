<?php

namespace App\Enums;

enum IncidentAnalysisStatus: string
{
    case NotStarted = 'not_started';
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
}
