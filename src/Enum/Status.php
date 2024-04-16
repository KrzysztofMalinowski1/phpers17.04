<?php

declare(strict_types=1);

namespace App\Enum;

enum Status: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case WAITING_FOR_CONFIRMATION = 'waiting_for_confirmation';
    case PAID = 'paid';

    case COMPLETED = 'completed';
    case FAILURE = 'failure';
}
