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