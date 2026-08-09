<?php

namespace App\Enums\Store;

enum OrderStatus: string
{
    case DRAFT     = 'draft';
    case PENDING   = 'pending';
    case PAID      = 'paid';
    case SHIPPED   = 'shipped';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}


