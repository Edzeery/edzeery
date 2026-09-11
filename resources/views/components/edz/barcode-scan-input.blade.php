@props([
    'wireScanMethod' => '',
    'label' => null,
    'placeholder' => '',
    'autofocus' => false,
])

@php
    // Only ever interpolate a bare PHP-style identifier into the Livewire
    // call sites below — anything else stays inert (no crash, no injection).
    $scanMethodValid = (bool) preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', (string) $wireScanMethod);
@endphp

<div
    x-data="barcodeScanInput({ scanMethod: @js($wireScanMethod) })"
    @edz-modal-closed="closeCamera()"
>
    @if ($label)
        <label class="edz-label mb-1.5 block">{{ $label }}</label>
    @endif

    <div class="flex items-stretch gap-2">
        <input
            type="text"
            @if ($scanMethodValid)
                @keydown.enter.prevent="$wire.{{ $wireScanMethod }}($event.target.value); $event.target.value = ''"
            @endif
            placeholder="{{ $placeholder }}"
            @if ($autofocus)
                autofocus
            @endif
            {{ $attributes->merge(['class' => 'edz-input w-full']) }}
        />

        <button
            type="button"
            @click="openCamera()"
            class="edz-btn edz-btn--primary shrink-0"
            aria-label="{{ __('edz.scan_with_camera') }}"
            title="{{ __('edz.scan_with_camera') }}"
        >
            <x-edz.icon name="camera" class="w-5 h-5" />
            <span class="ms-1.5 hidden sm:inline">{{ __('edz.scan_with_camera') }}</span>
        </button>
    </div>

    {{-- Camera modal: mounted on demand, so no stream is ever started until the
         user actually opens it. Everything below is Alpine-managed, so Livewire
         round-trips (e.g. wire:model updates) never morph the live scanner DOM. --}}
    <div wire:ignore>
        <template x-if="cameraOpen">
            <x-edz.modal :is-open="true" size="md">
                <div class="p-4 pt-1 sm:p-6 sm:pt-5">
                    <span class="edz-modal__handle" aria-hidden="true"></span>

                    <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-ink">
                        <x-edz.icon name="qr-code" class="w-5 h-5" />
                        <span>{{ __('edz.scan_with_camera') }}</span>
                    </h3>

                    {{-- Finder viewport; kept sized at all times so html5-qrcode
                         can measure it before start() completes. --}}
                    <div
                        x-ref="cameraContainer"
                        class="relative w-full aspect-video max-h-[55vh] overflow-hidden rounded-lg bg-ink/5"
                    >
                        <div
                            x-show="!scanning && cameraError === ''"
                            class="absolute inset-0 flex items-center justify-center gap-2 text-sm text-ink-muted"
                        >
                            <span class="edz-spinner" aria-hidden="true"></span>
                            <span>{{ __('edz.starting_camera') }}</span>
                        </div>

                        <div
                            x-show="cameraError !== ''"
                            class="absolute inset-0 flex items-center justify-center p-4"
                        >
                            <div class="w-full">
                                <x-edz.alert type="danger">
                                    {{ __('edz.camera_unavailable') }}
                                </x-edz.alert>
                            </div>
                        </div>
                    </div>

                    {{-- Device picker: only when the browser reports > 1 camera. --}}
                    <template x-if="cameras.length > 1">
                        <div class="mt-3 flex items-center gap-2">
                            <label for="edz-camera-device" class="edz-label shrink-0">
                                {{ __('edz.switch_camera') }}
                            </label>
                            <select
                                id="edz-camera-device"
                                class="edz-input w-full"
                                @change="selectCamera($event.target.value)"
                            >
                                <option value="" :selected="!selectedCameraId">
                                    {{ __('edz.auto_camera') }}
                                </option>
                                <template x-for="(cam, i) in cameras" :key="cam.id">
                                    <option :value="cam.id" :selected="selectedCameraId === cam.id" x-text="cam.label"></option>
                                </template>
                            </select>
                        </div>
                    </template>
                </div>
            </x-edz.modal>
        </template>
    </div>
</div>