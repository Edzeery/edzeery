<?php

namespace App\Livewire\Concerns;

/**
 * Product filter for the merchant dashboard confirmation section.
 *
 * Reuses HasOrderProductPicker::pickerProductResults() (the shared server pool
 * of active products) but NEVER writes into $this->form: this is a read-only
 * existence filter, so toggling a product simply appends/removes its product_id
 * in $this->productIds (defined by DashboardFilterConcern) which then flows into
 * the DashboardFilter and the confirmation stats query as an EXISTS clause.
 *
 * The picker pool row already carries product id, variant ids and count, so the
 * dashboard Blade can render chips and the "every variant included" hint without
 * any extra query. State props live in the Volt view's state() array; this trait
 * only contributes methods.
 */
trait DashboardProductFilterConcern
{
    use HasOrderProductPicker;

    public function openProductFilter(): void
    {
        $this->showProductFilterModal = true;

        if ($this->filterProductResults === []) {
            $this->loadFilterProducts();
        }
    }

    public function closeProductFilter(): void
    {
        $this->showProductFilterModal = false;
    }

    /** Load the first picker chunk (one request) and reset the chunk cursor. */
    public function loadFilterProducts(): void
    {
        $this->filterProductResults = $this->pickerProductResults(100);
        $this->filterProductHasMore = count($this->filterProductResults) === 100;
        $this->filterProductChunkLoading = false;
    }

    /**
     * Append the next 100 products in one request when the current pool has
     * already been exhausted by the client-side search.
     */
    public function loadFilterProductChunk(): void
    {
        if ($this->filterProductChunkLoading || ! $this->filterProductHasMore) {
            return;
        }

        $this->filterProductChunkLoading = true;
        $offset = count($this->filterProductResults);
        $chunk = $this->pickerProductResults(100, $offset);

        $this->filterProductResults = array_values(array_merge($this->filterProductResults, $chunk));
        $this->filterProductHasMore = count($chunk) === 100;
        $this->filterProductChunkLoading = false;
    }

    /**
     * Toggle a product in the filter. A multi-variant product selects the
     * product itself (every item line matches by product_id); a detail-level
     * variant pick is a later refinement on top of the same productIds set.
     */
    public function toggleFilterProduct(string $productId): void
    {
        $this->productIds = in_array($productId, $this->productIds, true)
            ? array_values(array_diff($this->productIds, [$productId]))
            : [...$this->productIds, $productId];
    }

    public function removeFilterProduct(string $productId): void
    {
        $this->productIds = array_values(array_diff($this->productIds, [$productId]));
    }

    public function clearProductFilter(): void
    {
        $this->productIds = [];
    }
}