<div>
    <label class="edz-label" for="faq-title">{{ __('merchant_panel.section_title') }}</label>
    <input id="faq-title" type="text"
        wire:model="section_content.faq.title"
        class="edz-input" />
</div>
@foreach ([0, 1, 2] as $i)
    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700 space-y-3">
        <p class="text-xs font-medium text-ink-400 uppercase tracking-wider">{{ __('merchant_panel.faq_item') }} {{ $i + 1 }}</p>
        <div>
            <label class="edz-label text-xs" for="faq-item-{{ $i }}-question">{{ __('merchant_panel.question') }}</label>
            <input id="faq-item-{{ $i }}-question" type="text"
                wire:model="section_content.faq.items.{{ $i }}.question"
                class="edz-input" />
        </div>
        <div>
            <label class="edz-label text-xs" for="faq-item-{{ $i }}-answer">{{ __('merchant_panel.answer') }}</label>
            <textarea id="faq-item-{{ $i }}-answer"
                wire:model="section_content.faq.items.{{ $i }}.answer"
                class="edz-input" rows="2"></textarea>
        </div>
    </div>
@endforeach
