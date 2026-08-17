@props(['steps', 'currentStep'])

<div class="mb-8">
    <nav aria-label="Progress">
        <ol class="flex items-center">
            @foreach ($steps as $index => $step)
                @php
                    $stepNum = $index + 1;
                    $isComplete = $stepNum < $currentStep;
                    $isCurrent = $stepNum === $currentStep;
                    $isUpcoming = $stepNum > $currentStep;
                @endphp
                <li class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                    <div class="flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold
                            {{ $isComplete ? 'bg-success-600 text-white' : ($isCurrent ? 'bg-brand-600 text-white ring-4 ring-brand-100 dark:ring-brand-900/40' : 'bg-surface-secondary text-ink-muted border border-surface-border') }}">
                            @if ($isComplete)
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @else
                                {{ $stepNum }}
                            @endif
                        </span>
                        <span class="hidden text-sm font-medium sm:inline {{ $isCurrent ? 'text-brand-700 dark:text-brand-300' : ($isComplete ? 'text-success-700 dark:text-success-300' : 'text-ink-muted') }}">
                            {{ $step['label'] }}
                        </span>
                    </div>
                    @if (!$loop->last)
                        <div class="mx-3 h-0.5 flex-1 {{ $isComplete ? 'bg-success-300 dark:bg-success-700' : 'bg-surface-border' }}"></div>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
</div>
