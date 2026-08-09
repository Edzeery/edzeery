<?php

namespace App\Models\Stores;

use App\Enums\Store\StoreStatusEnum;
use App\Models\Traits\HasStoreDefaults;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class StoreUserRequest extends Model
{

    use HasUlids;
    //
    protected $fillable = [
        'user_id',
        'store_id',
        'title',
        'message',
        'status',
        'assigned_to',
    ];

    protected $casts = [
        'status' => StoreStatusEnum::class,
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
