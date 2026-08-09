<?php

namespace App\Enums\Store;

use App\Enums\Concerns\InteractsWithStatusKit;

enum OrderStatus: string
{
    use InteractsWithStatusKit;

    protected const GROUP = 'order';

    case DRAFT     = 'draft';
    case PENDING   = 'pending';
    case PAID      = 'paid';
    case SHIPPED   = 'shipped';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}


