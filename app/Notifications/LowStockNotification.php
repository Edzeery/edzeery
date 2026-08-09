<?php

namespace App\Notifications;

use App\Models\Products\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ProductVariant $variant
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Low Stock Alert')
            ->level('warning')
            ->line("Product: {$this->variant->product->name}")
            ->line("SKU: {$this->variant->sku}")
            ->line("Remaining stock: {$this->variant->stock}")
            ->action(
                'View Inventory',
                route('filament.user.resources.inventories.index')
            );
    }

    public function toArray($notifiable): array
    {
        return [
            'variant_id' => $this->variant->id,
            'sku' => $this->variant->sku,
            'stock' => $this->variant->stock,
            'threshold' => $this->variant->low_stock_threshold,
        ];
    }
}
