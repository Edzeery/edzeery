@props([])
{{-- Dark mode toggle — vanilla JS, no Alpine dependency --}}
<script>
    (function() {
        var t = localStorage.getItem('edz-theme');
        if (t === 'dark' || (!t && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
<button
    class="relative flex items-center justify-center
           text-gray-500 transition-colors
           bg-white border border-gray-200 rounded-full
           hover:text-gray-900 h-10 w-10
           hover:bg-gray-100 hover:text-gray-900
           dark:border-gray-700 dark:bg-gray-800
           dark:text-gray-400 dark:hover:bg-gray-700
           dark:hover:text-white"
    aria-label="Toggle dark mode"
    onclick="
        var html = document.documentElement;
        var isDark = html.classList.contains('dark');
        if (isDark) {
            html.classList.remove('dark');
            localStorage.setItem('edz-theme', 'light');
        } else {
            html.classList.add('dark');
            localStorage.setItem('edz-theme', 'dark');
        }
    ">
    <svg class="hidden dark:block" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="5"></circle>
        <line x1="12" y1="1" x2="12" y2="3"></line>
        <line x1="12" y1="21" x2="12" y2="23"></line>
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
        <line x1="1" y1="12" x2="3" y2="12"></line>
        <line x1="21" y1="12" x2="23" y2="12"></line>
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
    </svg>
    <svg class="dark:hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
    </svg>
</button>
