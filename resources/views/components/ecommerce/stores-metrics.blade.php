@props(['stores'])

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
    @foreach ($stores as $store)
        <form method="POST" action="{{ route('merchant.choose-store.select', $store['store_slug']) }}">
            @csrf

            <button type="submit"
                class="group w-full rounded-2xl border border-neutral-border
                 bg-white p-5 text-left transition-all duration-300
                  hover:-translate-y-1 hover:shadow-lg dark:border-dark-border
                   dark:bg-white/[0.03]">

                {{-- Header --}}
                <div class="flex items-center justify-between">
                    {{-- logo  --}}
                    <div
                        class="flex items-center justify-center w-12 h-12
                  text-ink
                   bg-gray-100 rounded-xl dark:bg-gray-800">
                        @if (isset($store['store_logo']))
                            <img src="{{ asset('storage/' . $store['store_logo']) }}" alt="User" />
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                class="bi bi-shop" viewBox="0 0 16 16">
                                <path
                                    d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.37 2.37 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0M1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5M4 15h3v-5H4zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zm3 0h-2v3h2z" />
                            </svg>
                        @endif

                    </div>
                    {{-- Store Info --}}
                    <div class="text-left space-y-1">
                        <div class="font-semibold text-ink">
                            {{ $store['store_name'] }}
                        </div>
                        <div class="flex justify-between gap-2 ">
                            <div class="text-xs text-neutral-soft dark:text-dark-soft">

                                <x-role-badge :role="$store['membership_role']" />

                            </div>

                            <x-status-badge domain="store" :status="$store['store_status']" />
                        </div>
                    </div>

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-neutral-soft transition group-hover:translate-x-1" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>

                {{-- Metrics --}}
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs text-neutral-soft">
                            {{ __('dashboard.total_memberships') }}
                        </p>
                        <h4 class="mt-1 text-lg font-bold text-ink">
                            {{ $store['members_count'] }}
                        </h4>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs text-neutral-soft">
                            {{ __('titles.plan') }}
                        </p>
                        <h4 class="mt-1 text-sm font-semibold text-ink">
                            {{ $store['plan_name'] }}
                        </h4>
                    </div>
                </div>
            </button>
        </form>
    @endforeach

    {{-- Create New Store --}}
    @if (user()?->canCreateMultiStore())
        <a href="{{ route('merchant.create-store') }}"
            class="flex min-h-[220px] flex-col items-center justify-center rounded-2xl border-2 border-dashed border-brand-300 bg-brand-50/40 p-6 text-center transition hover:bg-brand-50 dark:border-brand-700 dark:bg-brand-900/10">

            <div
                class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-md">
                +
            </div>

            <h3 class="text-lg font-bold text-brand-700 dark:text-brand-400">
                {{ __('buttons.create') }} {{ __('buttons.new') }}
            </h3>
        </a>
    @endif
</div>
