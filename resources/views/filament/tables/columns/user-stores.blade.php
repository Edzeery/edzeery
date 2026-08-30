@foreach ($getRecord()->stores as $store)
    <div class="flex items-center gap-2 ">

        <x-status-badge domain="stores" :status="$store->currentStatus()->value" :store-id="$store->getKey()" />
        <span>{{ $store->name }}</span>
    </div>
@endforeach
