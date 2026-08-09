<?php

namespace App\Filament\Merchant\Pages\Store;

use App\Enums\Store\StoreStatusEnum;
use App\Filament\Merchant\Clusters\Settings\SettingsCluster;
use App\Filament\Merchant\Pages\Store\Schemas\MyStoreInfolist;
use App\Models\Stores\Store;
use App\Services\Stores\SubscriptionAlertService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class MyStore extends Page
{
    use InteractsWithInfolists;

    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'My Store';
    protected static string|UnitEnum|null $navigationGroup = 'Store Management';

    protected static ?string $title = 'My Store';

    protected string $view = 'filament.merchant.pages.store.my-store';

    public ?Store $store = null;

    public function mount(): void
    {
        $this->store = currentStore();
        if (! $this->store) {
            redirect()->route('filament.user.pages.store-onboarding');
        }
    }

    // public $defaultAction = 'onboarding';
    public $defaultAction = 'subscriptionAlerts';
 
    protected function getHeaderActions(): array
    {
        return [

            Action::make('Status')
                ->label(__('titles.status'))
                ->link()
                ->url(fn() => route('filament.merchant.settings.pages.status', currentStore())),

            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil')
                ->url(fn() => route('filament.merchant.tenant.profile', [currentStore(), $this->store]))
                ->tooltip('يمكنك اجراء التعديل من هنا')
                ->visible(fn(): bool => true),

            Action::make('delete')
                ->requiresConfirmation()
                ->color('danger')
                ->action(fn() => $this->post->delete())
                ->visible(fn(): bool => true),
        ];
    }
    public function infolist(Schema $infolist): Schema
    {
        return MyStoreInfolist::make($infolist, $this->store);
    }
}
