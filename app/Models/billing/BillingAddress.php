<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class BillingAddress extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'name',
        'company',
        'vat_number',
        'country',
        'state',
        'city',
        'address_line_1',
        'address_line_2',
        'zip',
        'phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
