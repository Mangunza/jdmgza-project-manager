<?php

namespace App\Domains\Projects\Enums;

enum ProjectStatus: string
{
    case DRAFT = 'draft';
    case PLANNING = 'planning';
    case QUOTED = 'quoted';
    case APPROVED = 'approved';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
