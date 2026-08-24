<div>
    <label class="edz-label" for="social-proof-title">{{ __('merchant_panel.section_title') }}</label>
    <input id="social-proof-title" type="text"
        wire:model="section_content.social_proof.title"
        class="edz-input" />
</div>
@foreach ([0, 1, 2] as $i)
    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700 space-y-3">
        <p class="text-xs font-medium text-ink-400 uppercase tracking-wider">{{ __('merchant_panel.item') }} {{ $i + 1 }}</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="edz-label text-xs" for="social-proof-item-{{ $i }}-title">{{ __('merchant_panel.item_title') }}</label>
                <input id="social-proof-item-{{ $i }}-title" type="text"
                    wire:model="section_content.social_proof.items.{{ $i }}.title"
                    class="edz-input" />
            </div>
            <div>
                <label class="edz-label text-xs" for="social-proof-item-{{ $i }}-description">{{ __('merchant_panel.item_description') }}</label>
                <input id="social-proof-item-{{ $i }}-description" type="text"
                    wire:model="section_content.social_proof.items.{{ $i }}.description"
                    class="edz-input" />
            </div>
        </div>
    </div>
@endforeach
