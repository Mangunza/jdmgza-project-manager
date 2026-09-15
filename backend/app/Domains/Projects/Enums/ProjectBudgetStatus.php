<?php

namespace App\Domains\Projects\Enums;

enum ProjectBudgetStatus: string
{
    case NO_BUDGET = 'no_budget';
    case NORMAL = 'normal';
    case WARNING = 'warning';
    case CRITICAL = 'critical';
    case EXCEEDED = 'exceeded';
}
