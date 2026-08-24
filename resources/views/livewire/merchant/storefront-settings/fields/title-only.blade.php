{{-- Shared single-field editor for title-only sections (categories / brands / description) --}}
<div>
    <label class="edz-label" for="{{ $path }}-title">{{ __('merchant_panel.section_title') }}</label>
    <input id="{{ $path }}-title" type="text"
        wire:model="{{ $path }}.title"
        class="edz-input" />
</div>
