@foreach ($getRecord()->stores as $store)
    <div class="flex items-center gap-2 ">

        <x-status-badge :status="$store->currentStatus()" :iconOnly="true"/>
        <span>{{ $store->name }}</span>
    </div>
@endforeach
