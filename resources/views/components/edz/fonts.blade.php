@props([
    'weights' => '300;400;500;600;700',
])

@php
    $fontsUrl = 'https://fonts.googleapis.com/css2?family=Inter:wght@' . $weights . '&family=IBM+Plex+Sans+Arabic:wght@' . $weights . '&display=swap';
@endphp

{{--
    Non-render-blocking Google Fonts loader.
    Replaces the legacy blocking @import url(...) in app.css /
    base/_typography.scss (which also carried an invalid 590 weight).
    Used by the landing, guest, storefront and panel shells.
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="{{ $fontsUrl }}"
    media="print" onload="this.media='all'">
<noscript>
    <link rel="stylesheet" href="{{ $fontsUrl }}">
</noscript>