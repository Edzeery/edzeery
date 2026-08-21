<?php

namespace App\Domains\User\Actions;

use App\Domains\User\Events\UserCreated;
use App\Models\User;

class CreateUserAction
{
    public function execute(array $data): User
    {
        $user = User::create($data);
        event(new UserCreated($user));
        return $user;
    }
}
