{{-- Shared length-counted field used by every section editor so native
     maxlength + the live counter always mirror StorefrontSections::TEXT_LIMITS.

     Counter is fully self-contained: state lives on this wrapper, init reads
     the DOM once, typing updates it. No $refs / cross-node reads at render
     time, so Livewire morphs can never break it.

     Expects: $id, $label, $wirePath, $max
     Optional: $type ('input'|'textarea', default input), $rows, $placeholder --}}
@php
    $type = $type ?? 'input';
@endphp
<div>
    <label class="edz-label" for="{{ $id }}">{{ $label }}</label>
    @if ($type === 'textarea')
        <div class="relative" x-data="{ len: 0 }"
            x-init="len = ($el.querySelector('textarea')?.value ?? '').length">
            <textarea id="{{ $id }}" rows="{{ $rows ?? 2 }}" maxlength="{{ $max }}"
                x-on:input="len = $el.value.length"
                wire:model="{{ $wirePath }}"
                class="edz-input pe-14"
                placeholder="{{ $placeholder ?? '' }}"></textarea>
            <span class="pointer-events-none absolute bottom-2 end-3 flex items-center text-xs text-ink-400" aria-hidden="true">
                <span x-text="len"></span>/<span>{{ $max }}</span>
            </span>
        </div>
    @else
        <div class="relative" x-data="{ len: 0 }"
            x-init="len = ($el.querySelector('input')?.value ?? '').length">
            <input id="{{ $id }}" type="text" maxlength="{{ $max }}"
                x-on:input="len = $el.value.length"
                wire:model="{{ $wirePath }}"
                class="edz-input pe-16"
                placeholder="{{ $placeholder ?? '' }}" />
            <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-xs text-ink-400" aria-hidden="true">
                <span x-text="len"></span>/<span>{{ $max }}</span>
            </span>
        </div>
    @endif
</div>
