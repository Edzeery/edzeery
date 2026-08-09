@props([
    'stats' => [],
])

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
    @foreach($stats as $key => $stat)
        <x-ecommerce.metric-card
            :icon="$stat['icon']"
            :title="$stat['title']"
            :count="$stat['count']"
            :desc="$stat['desc'] ?? ''"
            :percentage-result="$stat['percentage_result'] ?? null"
            :trend="$stat['trend'] ?? null"
            :icon="$stat['icon'] ?? null"
        />
    @endforeach
</div>
