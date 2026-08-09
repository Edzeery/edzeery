<?php

namespace App\Domains\User\Actions;

use App\Domains\User\Events\UserCreated;
use App\Domains\User\Models\User;
use App\Models\User as ModelsUser;

class CreateUserAction
{
    public function execute(array $data): ModelsUser
    {
        $user = ModelsUser::create($data);
        event(new UserCreated($user));
        return $user;
    }
}
