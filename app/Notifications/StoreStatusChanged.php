<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Enums\Store\StoreStatusEnum;
use App\Models\Stores\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class StoreStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected Store $store;
    protected StoreStatusEnum $status;
    protected ?string $reason;

    public function __construct(Store $store, StoreStatusEnum $status, ?string $reason = null)
    {
        $this->store = $store;
        $this->status = $status;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'store_id' => $this->store->id,
            'store_name' => $this->store->name,
            'status' => $this->status->value,
            'label' => $this->status->getLabel(),
            'reason' => $this->reason,
        ];
    }
}
