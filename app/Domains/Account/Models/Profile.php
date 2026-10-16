<?php

namespace App\Domains\Account\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'user_id';

    protected $table = 'profiles';

    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'address',
        'birthdate',
        'profile_picture',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function avatarUrl(): ?string
    {
        if (! $this->profile_picture) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->profile_picture);
    }
}
