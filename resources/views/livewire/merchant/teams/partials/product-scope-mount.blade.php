{{-- Product-scope modal mount (P36.3) — extracted to keep teams/index.blade.php within the Volt cap. --}}
@if ($this->productScopeMembershipId)
    <div @product-scope-closed.window="$wire.closeProductScope()">
        @livewire('merchant.teams.partials.product-scope-modal', ['membershipId' => $this->productScopeMembershipId], key('scope-' . $this->productScopeMembershipId))
    </div>
@endif