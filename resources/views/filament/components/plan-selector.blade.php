<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach ($plans as $plan)
        <div
            x-data
            class="border rounded-xl p-6 cursor-pointer transition
                   hover:shadow-lg
                   {{ $getState('plan_id') == $plan->id ? 'ring-2 ring-brand-500' : '' }}"
            @click="$wire.set('data.plan_id', {{ $plan->id }})"
        >
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-bold">{{ $plan->name }}</h3>

                @if($plan->is_default)
                    <span class="text-xs px-2 py-1 rounded bg-brand-500 text-white">
                        Most Popular
                    </span>
                @endif
            </div>

            <div class="text-2xl font-extrabold mb-4">
                {{ $plan->price }} {{ $plan->currency }}
                <span class="text-sm text-gray-500">
                    / {{ $plan->duration }} days
                </span>
            </div>

            <ul class="space-y-2 text-sm text-gray-600">
                @foreach ($plan->features as $feature)
                    <li>✔ {{ $feature->name }}:
                        {{ $feature->pivot->value }} {{ $feature->unit }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
