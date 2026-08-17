@props([
    'title',
    'description' => null,
])

<div class="edz-page-head">
    <div>
        <h1 class="edz-page-head__title">{{ $title }}</h1>
        @if ($description)
            <p class="edz-page-head__subtitle">{{ $description }}</p>
        @endif
    </div>
    @if (isset($actions) && $actions)
        <div class="edz-page-head__actions">
            {{ $actions }}
        </div>
    @endif
</div>
