<?php

namespace App\Policies;

use App\Models\Products\ProductOption;
use App\Models\User;

class ProductOptionPolicy
{
    public function delete(User $user, ProductOption $option): bool
    {
        // ممنوع الحذف إذا مستخدم في Variants
        return ! $option->isUsedInVariants();
    }
}
