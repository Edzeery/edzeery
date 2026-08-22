<?php

namespace App\Domains\Billing\Events;

use App\Models\billing\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Payment $payment,
    ) {}
}
