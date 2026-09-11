@props(['steps', 'currentStep', 'lockedSteps' => []])

<div class="mb-8">
    <nav aria-label="Progress">
        <ol class="flex items-center">
            @foreach ($steps as $step)
                @php
                    $stepNum = $step['id'];
                    $isLocked = in_array($stepNum, $lockedSteps, true);
                    $isComplete = $stepNum < $currentStep && ! $isLocked;
                    $isCurrent = $stepNum === $currentStep;
                    $isUpcoming = $stepNum > $currentStep;
                @endphp
                <li class="flex items-center {{ ! $loop->last ? 'flex-1' : '' }}">
                    @if ($isLocked)
                        <span class="flex cursor-not-allowed items-center gap-2" aria-disabled="true">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full border border-surface-border bg-surface-secondary text-sm font-semibold text-ink-muted opacity-60">
                                <x-edz.icon name="lock-closed" class="h-4 w-4" />
                            </span>
                            <span class="hidden text-sm font-medium text-ink-muted opacity-60 sm:inline">{{ $step['label'] }}</span>
                        </span>
                    @else
                        <button type="button"
                                @click="$wire.goToStep({{ $stepNum }})"
                                class="group flex items-center gap-2 text-start"
                                :aria-current="{{ $isCurrent ? "'step'" : 'false' }}">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold
                                {{ $isComplete ? 'bg-success-fg text-white' : ($isCurrent ? 'bg-brand-fg text-white ring-4 ring-brand-ring' : 'border border-surface-border bg-surface-secondary text-ink-muted group-hover:text-brand-fg-strong') }}">
                                @if ($isComplete)
                                    <x-edz.icon name="check-circle" class="h-5 w-5" />
                                @else
                                    {{ $stepNum }}
                                @endif
                            </span>
                            <span class="hidden text-sm font-medium sm:inline
                                {{ $isCurrent ? 'text-brand-fg' : ($isComplete ? 'text-success-fg' : 'text-ink-muted group-hover:text-ink') }}">
                                {{ $step['label'] }}
                            </span>
                        </button>
                    @endif
                    @if (! $loop->last)
                        <div class="mx-3 h-0.5 flex-1 {{ ($isComplete && ! $isLocked) ? 'bg-success-fg' : 'bg-surface-border' }}"></div>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
</div>