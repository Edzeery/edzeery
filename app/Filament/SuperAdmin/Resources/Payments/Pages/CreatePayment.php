<?php

namespace App\Filament\SuperAdmin\Resources\Payments\Pages;

use App\Filament\SuperAdmin\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}
