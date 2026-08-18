<?php

namespace App\Models;

use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
      use HasUlids;
    use SoftDeletes;
    protected $fillable = [
        'store_id',
        'name',
        'phone',
        'email',
        'address',
        'country_id',
        'state_id',
        'city_id',
        'status',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
