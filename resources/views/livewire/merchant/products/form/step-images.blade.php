<div x-show="step === 2" x-transition.opacity>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title">{{ __('products.images') }}</h2>
                        <p class="text-sm text-ink-400">{{ __('products.images_hint') }}</p>
                    </div>
                </div>
                <div class="edz-card__body space-y-4">
                    @if (count($images))
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($images as $index => $image)
                                @php
                                    $isTemp = $image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
                                    $src = $isTemp
                                        ? $image->temporaryUrl()
                                        : \Illuminate\Support\Facades\Storage::disk('public')->url($image);
                                    $isMain = $index === 0;
                                @endphp
                                <div class="relative overflow-hidden rounded-lg border {{ $isMain ? 'border-brand-500 ring-2 ring-brand-500/40' : 'border-surface-border' }}">
                                    <img src="{{ $src }}" alt="" class="h-24 w-full object-cover">

                                    @if ($isMain)
                                        <span class="absolute inset-x-0 bottom-0 flex items-center justify-center gap-1 bg-brand-600/95 py-1 text-xs font-semibold text-white">
                                            <x-edz.icon name="star" class="h-3 w-3 text-warning-400" />
                                            {{ __('products.main_image') }}
                                        </span>
                                    @else
                                        <button type="button" wire:click="makeMainImage({{ $index }})"
                                                title="{{ __('products.set_as_main') }}"
                                                class="absolute left-1 top-1 rounded-full bg-surface/90 p-1 text-warning-500 hover:bg-warning-100 hover:text-warning-600">
                                            <x-edz.icon name="star" class="h-3.5 w-3.5" />
                                        </button>
                                    @endif

                                    <button type="button" wire:click="removeImage({{ $index }})"
                                            class="absolute right-1 top-1 rounded-full bg-surface/90 px-2 py-0.5 text-xs font-semibold text-danger-600 hover:bg-danger-600 hover:text-white">
                                        &times;
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-ink-muted">{{ __('products.no_images') }}</p>
                    @endif

                    <input type="file" wire:model="images" multiple accept="image/*" wire:loading.attr="disabled"
                           onclick="this.value=''"
                           class="block w-full text-sm text-ink-soft file:mr-3 file:rounded-md file:border-0 file:bg-brand-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-brand-700">
                </div>
            </div>
        </div>
    </div>
</div>