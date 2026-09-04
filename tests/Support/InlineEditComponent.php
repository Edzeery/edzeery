<?php

namespace Tests\Support;

use App\Enums\Store\StorePermissionEnum;
use App\Livewire\Concerns\HasInlineEdit;
use App\Models\Brand;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Minimal Livewire component used ONLY by the HasInlineEdit feature tests to
 * exercise the trait through a realistic "inline edit a brand name" scenario.
 */
class InlineEditComponent extends Component
{
    use HasInlineEdit;

    public ?Brand $brand = null;

    public function mount(int|string $brandId): void
    {
        $this->brand = $this->resolveBrand($brandId);
    }

    public function startEditName(int|string $brandId): void
    {
        $brand = $this->resolveBrand($brandId);

        $this->startEdit(
            field: 'brand.name',
            recordId: $brand->id,
            currentValue: $brand->name,
        );
    }

    public function saveName(): void
    {
        $this->saveEdit([
            'field' => 'brand.name',
            'permission' => StorePermissionEnum::PRODUCT_UPDATE->value,
            'rules' => ['value' => ['required', 'string', 'min:3']],
            'subject' => fn (mixed $id) => $this->resolveBrand($id),
            'apply' => function (Brand $brand, mixed $value) {
                $brand->update(['name' => $value, 'slug' => Str::slug($value)]);
            },
            'label' => 'brand name',
            'audit_event' => 'brand_renamed',
        ]);
    }

    public function cancelName(): void
    {
        $this->cancelEdit();
    }

    protected function resolveBrand(mixed $id): Brand
    {
        return Brand::where('store_id', currentStoreId())->findOrFail($id);
    }

    public function render()
    {
        return view('tests-support.inline-edit-component');
    }
}
