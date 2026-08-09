@if(count($issues))
    <ul class="space-y-2">
        @foreach($issues as $issue)
            <li class="p-2 bg-red-100 text-red-800 rounded">
                <strong>{{ $issue['store']->name }}:</strong> {{ $issue['message'] }}
            </li>
        @endforeach
    </ul>
@else
    <p class="text-green-600">All subscriptions are active.</p>
@endif
