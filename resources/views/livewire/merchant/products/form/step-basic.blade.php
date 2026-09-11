<div x-show="step === 1" x-transition.opacity>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title">{{ __('products.basic_information') }}</h2>
                        <p class="text-sm text-ink-400">{{ __('products.basic_information_hint') }}</p>
                    </div>
                </div>
                <div class="edz-card__body grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="edz-field md:col-span-2">
                        <label class="edz-field__label" for="product-name">{{ __('products.name') }}</label>
                        <input id="product-name" type="text" class="edz-input @error('name') edz-input--error @enderror"
                               wire:model.live="name" placeholder="e.g. Premium Cotton T-Shirt">
                        @error('name')
                            <span class="edz-field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="edz-field md:col-span-2">
                        <label class="edz-field__label" for="product-slug">{{ __('products.slug') }}</label>
                        <input id="product-slug" type="text" class="edz-input @error('slug') edz-input--error @enderror"
                               wire:model="slug" placeholder="premium-cotton-t-shirt">
                        @error('slug')
                            <span class="edz-field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="edz-field">
                        <label class="edz-field__label" for="product-brand">{{ __('products.brand') }}</label>
                        <x-edz.select
                            wire:model="brand_id"
                            :options="$this->brands"
                            placeholder="{{ __('products.no_brand') }}"
                        />
                    </div>

                    <div class="edz-field">
                        <label class="edz-field__label" for="product-unit">{{ __('products.unit') }}</label>
                        <input id="product-unit" type="text" class="edz-input" wire:model="unit"
                               placeholder="e.g. pcs, kg, box">
                    </div>

                    <div class="edz-field md:col-span-2">
                        <label class="edz-field__label" for="product-categories">{{ __('products.categories') }}</label>
                        <select id="product-categories" class="edz-select" wire:model="categories" multiple size="4">
                            @foreach ($this->categoryOptions as $id => $categoryName)
                                <option value="{{ $id }}" @selected(in_array($id, $categories))>{{ $categoryName }}</option>
                            @endforeach
                        </select>
                        <p class="edz-field__hint">{{ __('products.categories_hint') }}</p>
                    </div>

                    <div class="edz-field md:col-span-2">
                        <label class="edz-field__label" for="product-short-description">{{ __('products.short_description') }}</label>
                        <textarea id="product-short-description" class="edz-textarea" wire:model="short_description"
                                  rows="2" placeholder="{{ __('products.short_description_placeholder') }}"></textarea>
                    </div>

                    <div class="edz-field md:col-span-2">
                        <label class="edz-field__label" for="product-description">{{ __('products.description') }}</label>
                        <textarea id="product-description" class="edz-textarea" wire:model="description"
                                  rows="6" placeholder="{{ __('products.description_placeholder') }}"></textarea>
                    </div>

                    <label class="flex items-center gap-2 text-sm font-medium text-ink">
                        <input type="checkbox" wire:model.live="is_active" class="h-4 w-4 rounded border-surface-border text-brand-600">
                        {{ __('products.active') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm font-medium text-ink">
                        <input type="checkbox" wire:model.live="is_featured" class="h-4 w-4 rounded border-surface-border text-brand-600">
                        {{ __('products.featured') }}
                    </label>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title">{{ __('products.images') }}</h2>
                        <p class="text-sm text-ink-400">{{ __('products.images_hint') }}</p>
                    </div>
                </div>
                <div class="edz-card__body space-y-4">
                    @if (count($images) || count($newImages))
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($images as $index => $path)
                                <div class="relative overflow-hidden rounded-lg border border-surface-border">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}" alt=""
                                         class="h-24 w-full object-cover">
                                    <button type="button" wire:click="removeImage({{ $index }})"
                                            class="absolute right-1 top-1 rounded-full bg-surface/90 px-2 py-0.5 text-xs font-semibold text-danger-600 hover:bg-danger-600 hover:text-white">
                                        &times;
                                    </button>
                                </div>
                            @endforeach
                            @foreach ($newImages as $index => $upload)
                                <div class="relative overflow-hidden rounded-lg border border-surface-border">
                                    <img src="{{ $upload->temporaryUrl() }}" alt=""
                                         class="h-24 w-full object-cover">
                                    <button type="button" wire:click="removeNewImage({{ $index }})"
                                            class="absolute right-1 top-1 rounded-full bg-surface/90 px-2 py-0.5 text-xs font-semibold text-danger-600 hover:bg-danger-600 hover:text-white">
                                        &times;
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <input type="file" wire:model="newImages" multiple accept="image/*"
                           class="block w-full text-sm text-ink-soft file:mr-3 file:rounded-md file:border-0 file:bg-brand-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-brand-700">
                </div>
            </div>
        </div>
    </div>
</div>