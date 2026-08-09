<?php

namespace App\Enums\Store;

use App\Enums\Concerns\InteractsWithStatusKit;
use App\Enums\Concerns\InteractsWithStatusPresentation;

enum StoreStatusEnum: string
{
    use InteractsWithStatusKit; // Auto Detect Group
    use InteractsWithStatusPresentation;

    protected const GROUP = 'stores';


    case ACTIVE = 'active'; // المتجر يعمل
    case PENDING = 'pending'; // بانتظار المراجعة
    case SUSPENDED = 'suspended'; // موقوف
    case CLOSED = 'closed'; //مغلق
    case BLOCKED = 'blocked'; //
    case DRAFT = 'draft'; // غير منشور
    case APPROVED = 'approved'; // مقيول
    case REJECTED = 'rejected'; // مرفوض

    public function getLabel(): string
    {
        return $this->label();
    }

}
