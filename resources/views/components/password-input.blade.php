@props([
    'id' => 'password',
    'name' => 'password',
    'label' => null,
    'value' => '',
    'autocomplete' => 'current-password',
    'required' => false,
    'disabled' => false,
])

<div>
    @if ($label)
        <x-input-label :for="$id" :value="$label" />
    @endif

    <div class="relative mt-1" x-data="{ show: false }">
        <input @disabled($disabled) id="{{ $id }}" name="{{ $name }}" type="password"
            :type="show ? 'text' : 'password'" value="{{ $value }}"
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($required) required @endif
            {{ $attributes->merge([
                'class' =>
                    'w-full px-4 py-2.5 pe-11 rounded-xl border border-surface-border
                     bg-surface text-ink text-sm
                     placeholder:text-ink-soft placeholder:font-normal
                     focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500
                     disabled:opacity-60 disabled:cursor-not-allowed
                     transition-all duration-200',
            ]) }} />

        <button type="button" @click="show = !show" x-bind:aria-pressed="show.toString()"
            aria-label="{{ __('auth.toggle_password_visibility') }}"
            class="absolute inset-y-0 end-0 flex items-center px-3 text-ink-soft hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/30 rounded-e-xl transition">
            <x-edz.icon name="eye" class="w-5 h-5" x-show="!show" />
            <x-edz.icon name="eye-slash" class="w-5 h-5" x-show="show" x-cloak />
        </button>
    </div>
</div>
