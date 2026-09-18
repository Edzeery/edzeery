<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    use App\Livewire\Concerns\HasOrderProductPicker;

    use App\Livewire\Concerns\TrackingGridConcern;

    use App\Livewire\Concerns\TrackingColumnConcern;

    use App\Livewire\Concerns\TrackingDrawerConcern;

    use App\Livewire\Concerns\TrackingRiderFormConcern;

    use App\Livewire\Concerns\TrackingTrashConcern;

    use App\Livewire\Concerns\TrackingFilterConcern;

    use App\Livewire\Concerns\TrackingBulkValidateConcern;

    use App\Livewire\Concerns\TrackingReassignConcern;

    public $filters;

    public $search;

    public $filteredTotal;

    public $shipments;

    public $page;

    public $perPage;

    public $allProviders;

    public $allCities;

    public $allMembers;

    public $allStates;

    public $allProducts;

    public $searchableRiders;

    public $stats;

    public $drawerOrderId;

    public $drawerTracking;

    public $drawerStatusHistories;

    public $drawerEvents;

    public $canViewDrawerEvents;

    public $noteDraft;

    public $sendingNote;

    public $statusHistoryFor;

    public $statusHistory;

    public $statusHistoryMeta;

    public $shipmentNotesFor;

    public $shipmentNotes;

    public $shipmentNotesMeta;

    public $showBulkValidateModal;

    public $bulkValidateAnalysis;

    public $bulkValidateReadyCount;

    public $bulkValidateSkipCount;

    public $bulkValidateBusy;

    public $trackingTab;

    public $showTrash;

    public $trashCount;

    public $labelOpen;

    public $labelOrderId;

    public $labelData;

    public $allRiders;

    public $riderRiders;

    public $riderOptions;

    public $riderStatsActiveCount;

    public $riderStatsActiveShipments;

    public $riderStatsCodDueToday;

    public $visibleColumns;

    public $draftColumns;

    public $showTableSettings;

    public $tableStyle;

    public $draftStyle;

    public $showCreateModal;

    public $showEditModal;

    public $showProductPickerModal;

    public $showVariantPickerModal;

    public $editingOrderId;

    public $form;

    public $formProductResults;

    public $formProductView;

    public $formSelectedProduct;

    public $formSelectedItems;

    public $formPartnerType;

    public $productChunkLoading;

    public $productHasMore;

    public $formOffices;

    public $loadingOffices;

    public $formOfficesVersion;

    public $formHasOffices;

    public $formCities;

    public $formAvailableStates;

    public $formCoverageHint;

    public $formDuplicateWarnings;

    public $trackingReassignOpen;

    public $trackingReassignId;

    public $trackingReassignMembershipId;

    public $trackingReassignCandidates;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function updated($name)
    {
        $arguments = [static::$__context, $this, array_slice(func_get_args(), 1)];

        return (new Actions\CallPropertyHook('updated', $name))->execute(...$arguments);
    }

};