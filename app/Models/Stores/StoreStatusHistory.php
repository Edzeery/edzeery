<?php

namespace App\Models\Stores;

use App\Enums\Store\StoreStatusEnum;
use App\Models\Traits\HasStoreDefaults;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class StoreStatusHistory extends Model
{
    //
    use HasUlids;
    protected $fillable = [
        'store_id',
        'status',
        'reason',
        'changed_by',
    ];

    protected $casts = [
        'status' => StoreStatusEnum::class,
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
