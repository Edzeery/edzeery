<div wire:key="ie-{{ $brand->id }}">
    <x-edz.inline-edit-field
        :field="'brand.name'"
        :value="$brand->name"
        :editing="($editingField ?? null) === 'brand.name'"
        :error="$editingError ?? null"
        :wire:start="'startEditName(' . $brand->id . ')'"
        :wire:save="'saveName'"
        :wire:cancel="'cancelName'"
        wire:model="editingValue" />
</div>
