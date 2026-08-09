<?php

namespace App\Domains\Merchant\DTOs;

use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Enums\Store\StoreStatusEnum;

final class StoreCardData
{
    public function __construct(
        public readonly string $membershipId,
        public readonly UserRoleEnum|StoreRoleEnum $membershipRole,
        public readonly string $storeName,
        public readonly ?string $storeLogo,
        public readonly string $planName,
        public readonly StoreStatusEnum  $storeStatus,
        public readonly int $membersCount,
        public readonly bool $canEnter,
    ) {}

    public function toArray(): array
    {
        return [
            'membership_id' => $this->membershipId,
            'membership_role' => $this->membershipRole,
            'store_name' => $this->storeName,
            'store_logo' => $this->storeLogo ,
            'plan_name' => $this->planName,
            'store_status' => $this->storeStatus,
            'members_count' => $this->membersCount,
            'can_enter' => $this->canEnter,
        ];
    }
}
