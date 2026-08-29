{{-- Serves the loading overlay plus the page's opted-in action registry. --}}
<script>window.EdzLoaderActions = @js(\App\Support\Loading\LoadingActions::all());</script>

{{-- Visibility is class-driven (edz-loader-overlay--visible) instead of
     x-show/x-cloak so a plain inline script in the layout can reveal the
     cover at HTML-parse time — before Livewire/Alpine boot at
     DOMContentLoaded and before any component paints. --}}
<div
    class="edz-loader-overlay"
    :class="overlay ? 'edz-loader-overlay--visible' : ''"
    :aria-hidden="overlay ? 'false' : 'true'"
    role="status"
    aria-live="polite"
>
    <span class="sr-only">{{ __('messages.loading') }}</span>
    <div class="edz-loader-overlay__inner">
        <div class="edz-loader-overlay__mark" aria-hidden="true">E</div>
        <p class="edz-loader-overlay__label" x-show="label" x-cloak x-text="label"></p>
    </div>
</div>