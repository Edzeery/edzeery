<?php

namespace App\Filament\Merchant\Pages;

use App\Enums\Store\StoreRoleEnum;
use App\Enums\Store\StoreStatusEnum;
use App\Filament\Resources\Stores\Schemas\StoreForm;
use App\Models\Plans\Plan;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\Stores\Team\StoreRole;
use App\Support\StoreContext;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Clock\now;

class CreateNewStore extends RegisterTenant
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'create-store';

    public ?array $data = [];
    public ?store $storeis;

    public function mount(): void
    {
        $this->form->fill([
            'plan_id' => Plan::where('is_default', true)->value('id'),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->link()
        ];
    }

    public static function getLabel(): string
    {
        return 'Create new Store';
    }

    public function getTitle(): string
    {
        return 'Create new store';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::SevenExtraLarge;
    }

    public function form(Schema $schema): Schema
    {
        return StoreForm::configure($schema);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('createStore')
                ->label('🚀 Launch My Store')
                ->color('primary')
                ->size('lg')
                ->action('createStore'),
        ];
    }

    /* ================= Store Creation ================= */

    public function createStore(): void
    {
        $this->validate();
        DB::transaction(function () {

            /** @var Store $store */
            $this->storeis = Store::create([
                'name' => $this->data['name'],
                'slug' => $this->data['slug'],
                'description' => $this->data['description'] ?? null,
                'logo' => uploadPath($this->data['logo'] ?? null),
                'cover' => uploadPath($this->data['cover'] ?? null),
                'status' => StoreStatusEnum::ACTIVE,
            ]);

            $this->storeis->settings()->create([
                'currency' => $this->data['currency'],
                'currency_symbol' => $this->data['currency_symbol'] ?? 'DA',
                'language' => $this->data['language'] ?? 'ar',
                'inventory_tracking' => $this->data['inventory_tracking'] ?? true,
                'guest_checkout' => $this->data['guest_checkout'] ?? true,
            ]);

            $this->storeis->seo()->create([
                'meta_title' => $this->data['meta_title'] ?? null,
                'meta_description' => $this->data['meta_description'] ?? null,
                'meta_keywords' => $this->data['meta_keywords'] ?? null,
                'og_image' => uploadPath($this->data['og_image'] ?? null),
            ]);

            $this->storeis->theme()->create([
                'theme' => $this->data['theme'] ?? 'default',
                'primary_color' => $this->data['primary_color'] ?? '#000000',
                'secondary_color' => $this->data['secondary_color'] ?? '#ffffff',
                'font_family' => $this->data['font_family'] ?? 'Cairo',
            ]);


            // $membership->user->guard_name = 'merchant';
            // $membership->user->assignRole(StoreRoleEnum::OWNER->value);

            $Membership =   StoreMembership::updateOrCreate(
                ['store_id' => $this->storeis->id, 'user_id' => $this->storeis->user_id],
                [
                    'invited_by' => $this->storeis->user_id,
                    'invited_at'  => now(),
                    'accepted_at' => now(),
                    'is_active' => true,
                ]
            );

            if ($Membership && !$this->storeis->user->hasRole(StoreRoleEnum::OWNER)) {
                $this->storeis->user->guard_name = 'merchant';
                $this->storeis->user->assignRole(StoreRoleEnum::OWNER);
            }

            /* ================= Subscription ================= */

            // $plan = Plan::findOrFail($this->data['plan_id']);

            // $this->storeis->subscription()->create([
            //     'plan_id' => $plan->id,
            //     'starts_at' => now(),
            //     'trial_ends_at' => now()->addDays(10),
            //     'is_trial' => true,
            //     'status' => 'active',
            // ]);

            // $plan = Plan::with('prices')->findOrFail($this->data['plan_id']);
            // $period = $this->data['billing_period'];

            // $price = $plan->priceFor($period);

            // $this->storeis->subscription()->create([
            //     'store_id' => $store->id,
            //     'plan_id' => $plan->id,
            //     'billing_period' => $period,
            //     'starts_at' => now(),
            //     'ends_at' => $price->endsAt(),
            //     'status' => 'active',
            // ]);
        });

        //  $this->storeis->users()->attach(auth()->user());

        session(['current_store_id' => $this->storeis->id]);
        Filament::setTenant($this->storeis);

        $this->redirect(
            route('filament.merchant.pages.dashboard', $this->storeis),
            navigate: true
        );
    }
}
