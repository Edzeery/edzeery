<?php

namespace App\Filament\Merchant\Pages\Store;

use App\Enums\Store\StoreStatusEnum;
use App\Filament\Merchant\Clusters\Settings\SettingsCluster;
use App\Filament\Merchant\Pages\Store\Schemas\StoreStatusInfolist;
use App\Models\Stores\Store;
use App\Services\Stores\SubscriptionAlertService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class StoreStatus extends Page
{
    use InteractsWithInfolists;
    protected static ?string $cluster = SettingsCluster::class;
    protected static ?string $navigationParentItem = "My Store";
    protected static null|string $navigationLabel = 'Store Status';
    protected static string|UnitEnum|null $navigationGroup = 'Store Management';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-circle';
    protected static ?string $slug = 'status';

    protected string $view = 'filament.merchant.pages.store.store-status';


    public function getTitle(): string | Htmlable
    {
        return __('Store Status');
    }
    public $defaultAction = 'subscriptionAlerts';



    // public function subscriptionAlertsAction(): Action
    // {
    //     return Action::make('subscriptionAlerts')
    //         ->modalHeading('Subscription Status')
    //         ->modalWidth('md')
    //         ->modalContent(fn() => view('filament.merchant.modals.subscription-alerts', [
    //             'issues' => SubscriptionAlertService::checkAllUserStores(auth()->user())
    //         ])) // <- هنا فقط View object
    //         ->visible(fn(): bool => auth()->user()->stores()->exists() && currentStore()?->currentStatus() !== StoreStatusEnum::ACTIVE);
    // }

    //     public function getHeading(): string
    // {
    //     return __('Custom Page Heading');
    // }

    public function getSubheading(): ?string
    {
        return __('Custom Page Subheading');
    }

    protected function getActions(): array
    {
        return [


            Action::make('status')
                ->label(fn() => currentStore()?->currentStatus()?->getLabel() ?? 'Unknown')
                ->icon(fn() => currentStore()?->currentStatus()?->icon() ?? 'heroicon-o-exclamation-circle')
                ->color(fn() => currentStore()?->currentStatus()?->filamentColor() ?? 'gray')
                ->disabled(),

            Action::make('retry_verification')
                ->label('Retry Verification')
                ->icon(Heroicon::OutlinedReceiptRefund)
                ->visible(fn() => currentStore()?->currentStatus() === StoreStatusEnum::PENDING)
                ->action(function () {
                    \App\Models\Stores\StoreUserRequest::create([
                        'user_id' => currentStore()?->user->id,
                        'store_id' => currentStore()->id,
                        'title' => 'Retry Store Verification',
                        'message' => 'Please re-verify my store.',
                        'status' => 'pending',
                    ]);
                    Notification::make()
                        ->title('Sent successfully')
                        ->success()
                        ->body('Verification request sent.')
                        ->send();
                }),

            Action::make('contact_support')
                ->label('Contact Support')
                ->icon('heroicon-o-chat-bubble-bottom-center-text')
                ->action(function () {
                    \App\Models\Stores\StoreUserRequest::create([
                        'user_id' => user()->id,
                        'store_id' => currentStore()->id,
                        'title' => 'Support Request for Store ' . currentStore()->name,
                        'message' => 'Please assist with my store status.',
                        'status' => 'pending',
                    ]);
                    Notification::make()
                        ->title('Sent successfully')
                        ->success()
                        ->body('Support request submitted.')
                        ->send();
                }),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return StoreStatusInfolist::make($infolist, currentStore());
    }
}
