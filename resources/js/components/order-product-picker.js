export default function orderProductPicker() {
    return {
        isAddingProduct: false,
        isLoadingVariants: false,
        searchTerm: '',
        visibleCount: 0,
        selectedItems: {},

        init() {
            this.$wire.on('selected-items-updated', (items) => {
                this.selectedItems = items;
            });
        },

        openProductPicker() {
            this.searchTerm = '';
            this.visibleCount = 0;
            this.$wire.set('showProductPickerModal', true);
            this.$wire.loadProducts();
            setTimeout(() => {
                const input = document.querySelector('[data-product-search-input]');
                if (input) input.focus();
            }, 500);
        },

        closeProductPicker() {
            this.$wire.set('showProductPickerModal', false);
            this.searchTerm = '';
            this.visibleCount = 0;
        },

        closeVariantPicker() {
            this.$wire.set('showVariantPickerModal', false);
            this.$wire.set('formProductView', 'list');
            this.$wire.set('formSelectedProduct', null);
        },

        selectProduct(productId) {
            if (this.isAddingProduct) return;
            this.isAddingProduct = true;
            this.$wire.addFormItem(productId).then(() => {
                this.isAddingProduct = false;
                this.closeProductPicker();
            });
        },

        openVariants(productId) {
            if (this.isLoadingVariants) return;
            this.isLoadingVariants = true;
            this.$wire.selectProduct(productId).then(() => {
                this.$wire.set('showProductPickerModal', false);
                this.$wire.set('showVariantPickerModal', true);
                this.isLoadingVariants = false;
            });
        },

        selectVariant(variantId) {
            if (this.isAddingProduct) return;
            this.isAddingProduct = true;
            this.$wire.addFormItem(variantId).then(() => {
                this.isAddingProduct = false;
                this.$wire.set('showVariantPickerModal', false);
                this.$wire.set('formProductView', 'list');
                this.$wire.set('formSelectedProduct', null);
            });
        },

        selectProductByBarcode(event) {
            const value = event.target.value.trim();
            if (!value || this.isAddingProduct) return;
            this.isAddingProduct = true;
            this.$wire.addFormItemByBarcode(value).then(() => {
                this.isAddingProduct = false;
                event.target.value = '';
            });
        },

        onSearchInput(event) {
            this.searchTerm = event.target.value;
            this.$nextTick(() => {
                let count = 0;
                document.querySelectorAll('[data-search]').forEach(el => {
                    if (el.style.display !== 'none') count++;
                });
                this.visibleCount = count;
            });
        },
    };
}
