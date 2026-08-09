

@props(['stores'])

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6">
    @foreach ($stores as $store)
        <div
            class="rounded-2xl    bg-white p-5
         border border-neutral-border dark:border-dark-border
          dark:bg-white/[0.03] md:p-6">
            <div
                class="flex items-center justify-center w-12 h-12
                  text-neutral-text dark:text-dark-text
                   bg-gray-100 rounded-xl dark:bg-gray-800">
                @if ($store->logo)
                    <img src="{{ $store->logo }}" alt="User" />
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                        class="bi bi-shop" viewBox="0 0 16 16">
                        <path
                            d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.37 2.37 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0M1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5M4 15h3v-5H4zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zm3 0h-2v3h2z" />
                    </svg>
                @endif

            </div>
            <div class="flex items-center justify-between">
                <h3 class="font-semibold mx-2
                 text-neutral-text dark:text-dark-text"
                 >{{ $store->name }}</h3>

                <x-status-badge :status="$store->currentStatus()"  />

            </div>

            <div class="flex items-end justify-between mt-5">
                <div>
                    <span
                        class="text-sm text-gray-500 dark:text-gray-400">{{ __('titles.products') ?? 'Total Products' }}</span>
                    <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">
                        {{ $store->products()->count() ?? 0 }}</h4>
                </div>
                <div>
                    <span
                        class="text-sm text-gray-500 dark:text-gray-400">{{ __('titles.numbers_agents') ?? 'Total number Agents' }}</span>
                    <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">
                        {{ $store->numberAgents() ?? 0 }}</h4>
                </div>
                <span
                    class="flex items-center gap-1 rounded-full bg-success-50 py-0.5 pl-2 pr-2.5 text-sm font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                    <form method="POST" action="{{ route('choose-store.select', $store) }}">
                        @csrf

                        <button type="submit"
                            class="
                                            w-full flex items-center justify-between
                                            p-4 rounded-xl
                                            border border-neutral-border dark:border-dark-border
                                            bg-neutral-secondary dark:bg-dark-secondary
                                            hover:bg-brand-soft dark:hover:bg-accent-strong
                                            transition shadow-soft
                                        ">

                            {{-- Subscription --}}
                            <div class="text-right space-y-1">
                                <div class="font-semibold text-neutral-text dark:text-dark-text">
                                    {{ $store?->user->latestSubscription()?->plan?->name }}
                                </div>

                                @php
                                    $status = $store?->user->latestSubscription()?->status;
                                @endphp

                                <div class="text-xs px-2 py-1 rounded-full {{ $status?->css() }}">

                                    {{ $status?->getLabel() ?? $status }}
                                </div>
                            </div>

                            {{-- Arrow --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-arrow-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8" />
                            </svg>
                        </button>
                    </form>
                </span>
            </div>
        </div>
    @endforeach
    {{-- <div
      class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6"
    >
      <div
        class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800"
      >
        <svg
          class="fill-gray-800 dark:fill-white/90"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M11.665 3.75621C11.8762 3.65064 12.1247 3.65064 12.3358 3.75621L18.7807 6.97856L12.3358 10.2009C12.1247 10.3065 11.8762 10.3065 11.665 10.2009L5.22014 6.97856L11.665 3.75621ZM4.29297 8.19203V16.0946C4.29297 16.3787 4.45347 16.6384 4.70757 16.7654L11.25 20.0366V11.6513C11.1631 11.6205 11.0777 11.5843 10.9942 11.5426L4.29297 8.19203ZM12.75 20.037L19.2933 16.7654C19.5474 16.6384 19.7079 16.3787 19.7079 16.0946V8.19202L13.0066 11.5426C12.9229 11.5844 12.8372 11.6208 12.75 11.6516V20.037ZM13.0066 2.41456C12.3732 2.09786 11.6277 2.09786 10.9942 2.41456L4.03676 5.89319C3.27449 6.27432 2.79297 7.05342 2.79297 7.90566V16.0946C2.79297 16.9469 3.27448 17.726 4.03676 18.1071L10.9942 21.5857L11.3296 20.9149L10.9942 21.5857C11.6277 21.9024 12.3732 21.9024 13.0066 21.5857L19.9641 18.1071C20.7264 17.726 21.2079 16.9469 21.2079 16.0946V7.90566C21.2079 7.05342 20.7264 6.27432 19.9641 5.89319L13.0066 2.41456Z"
            fill=""
          />
        </svg>
      </div>

      <div class="flex items-end justify-between mt-5">
        <div>
          <span class="text-sm text-gray-500 dark:text-gray-400">Orders</span>
          <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">5,359</h4>
        </div>

        <span
          class="flex items-center gap-1 rounded-full bg-error-50 py-0.5 pl-2 pr-2.5 text-sm font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500"
        >
          <svg
            class="fill-current"
            width="12"
            height="12"
            viewBox="0 0 12 12"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M5.31462 10.3761C5.45194 10.5293 5.65136 10.6257 5.87329 10.6257C5.8736 10.6257 5.8739 10.6257 5.87421 10.6257C6.0663 10.6259 6.25845 10.5527 6.40505 10.4062L9.40514 7.4082C9.69814 7.11541 9.69831 6.64054 9.40552 6.34754C9.11273 6.05454 8.63785 6.05438 8.34486 6.34717L6.62329 8.06753L6.62329 1.875C6.62329 1.46079 6.28751 1.125 5.87329 1.125C5.45908 1.125 5.12329 1.46079 5.12329 1.875L5.12329 8.06422L3.40516 6.34719C3.11218 6.05439 2.6373 6.05454 2.3445 6.34752C2.0517 6.64051 2.05185 7.11538 2.34484 7.40818L5.31462 10.3761Z"
              fill=""
            />
          </svg>

          9.05%
        </span>
      </div>
    </div> --}}
</div>
