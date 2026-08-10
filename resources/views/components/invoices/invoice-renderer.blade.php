@props(['invoice', 'forPdf' => false])

@switch($invoice->template->slug)
    @case('modern-minimalist')
        <x-invoices.templates.modern-minimalist :invoice="$invoice" :forPdf="$forPdf" />
        @break
    @case('classic-business')
        <x-invoices.templates.classic-business :invoice="$invoice" :forPdf="$forPdf" />
        @break
    @case('creative-agency')
        <x-invoices.templates.creative-agency :invoice="$invoice" :forPdf="$forPdf" />
        @break

    @case('corporate-blue')
        {{-- we'll create this next --}}
        <x-invoices.templates.classic-business :invoice="$invoice" :forPdf="$forPdf" />
        @break

    @default
        <x-invoices.templates.modern-minimalist :invoice="$invoice" :forPdf="$forPdf" />
@endswitch
