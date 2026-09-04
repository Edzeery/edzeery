@props([
    'editing' => false,
    'value' => null,
    'empty' => '—',
    'type' => 'text',
    'rows' => 3,
    'placeholder' => null,
    'id' => null,
    'error' => null,
    'icon' => null,
    'class' => '',
    'wire:start',
    'wire:save',
    'wire:cancel',
    'wire:model',
])

@php
    $uid = $id ?? 'edz-inline-edit-' . \Illuminate\Support\Str::random(8);
    $saveMethod = $attributes->get('wire:save');
    $startMethod = $attributes->get('wire:start');
    $cancelMethod = $attributes->get('wire:cancel');
    $hasError = $editing && filled($error);
@endphp

<div {{ $attributes->merge(['class' => "edz-inline-edit $class"])->whereDoesntStartWith('wire:') }}>

    @if ($editing)
        {{-- Edit mode (toggled by the parent Livewire component) --}}
        <div class="edz-inline-edit__edit" wire:key="{{ $uid }}-edit"
            x-data x-init="requestAnimationFrame(() => $refs.input?.focus())">
            @if ($type === 'textarea')
                <textarea rows="{{ $rows }}" x-ref="input" class="edz-inline-edit__input @if ($hasError) edz-inline-edit__input--error @endif"
                    id="{{ $uid }}" placeholder="{{ $placeholder }}"
                    {{ $attributes->whereStartsWith('wire:model') }}
                    x-on:keydown.escape.window="$wire.{{ $cancelMethod }}()"></textarea>
            @else
                <input type="{{ $type }}" x-ref="input" class="edz-inline-edit__input @if ($hasError) edz-inline-edit__input--error @endif"
                    id="{{ $uid }}" placeholder="{{ $placeholder }}"
                    {{ $attributes->whereStartsWith('wire:model') }}
                    x-on:keydown.escape="$wire.{{ $cancelMethod }}()">
            @endif

            <div class="edz-inline-edit__actions">
                <button type="button" class="edz-inline-edit__save"
                    {{ $attributes->whereStartsWith('wire:save') }} wire:loading.attr="disabled">
                    <x-edz.spinner wire:target="{{ $saveMethod }}" />
                    <span wire:loading.remove wire:target="{{ $saveMethod }}">Save</span>
                </button>
                <button type="button" class="edz-inline-edit__cancel" {{ $attributes->whereStartsWith('wire:cancel') }}>
                    Cancel
                </button>
            </div>

            @if ($hasError)
                <p class="edz-inline-edit__error">{{ $error }}</p>
            @endif
        </div>
    @else
        {{-- Display mode --}}
        <button type="button" class="edz-inline-edit__display"
            @if ($startMethod) wire:click="{{ $startMethod }}" @endif>
            @if ($icon)
                <x-edz.icon :name="$icon" class="edz-inline-edit__icon w-4 h-4" />
            @endif
            @if (is_null($value) || $value === '')
                <span class="edz-inline-edit__empty">{{ $empty }}</span>
            @else
                <span class="edz-inline-edit__value">{{ $value }}</span>
            @endif
        </button>
    @endif
</div>
