// Drag-and-drop reordering for the order column settings modal.
// The grid renders the full ordered column list; only rows with a checked
// checkbox are persisted in the visible order. On drop we send the new
// visible order to Livewire in a single request (reorderDraftColumns).
export default function orderColumnReorderDraft() {
    return {
        draggingKey: null,
        overKey: null,
        dropSide: "after",

        onDragStart(e) {
            const row = e.target.closest("[data-col-row]");
            if (!row) return;
            this.draggingKey = row.dataset.colKey;
            if (e.dataTransfer) {
                e.dataTransfer.effectAllowed = "move";
                try {
                    e.dataTransfer.setData("text/plain", this.draggingKey);
                } catch (_) {
                    /* some browsers block setData before dragstart */
                }
            }
            this.$el.querySelectorAll("[data-col-row]").forEach((r) => {
                r.classList.toggle("edz-drag-source", r === row);
            });
        },

        onDragOver(e) {
            e.preventDefault();
            if (e.dataTransfer) e.dataTransfer.dropEffect = "move";

            const row = e.target.closest("[data-col-row]");
            if (!row) return;

            const rect = row.getBoundingClientRect();
            const side = e.clientY < rect.top + rect.height / 2 ? "before" : "after";

            if (this.overKey !== row.dataset.colKey || this.dropSide !== side) {
                this.overKey = row.dataset.colKey;
                this.dropSide = side;
                this.$el.querySelectorAll("[data-col-row]").forEach((r) => {
                    r.classList.remove(
                        "edz-drag-over-before",
                        "edz-drag-over-after",
                    );
                });
                row.classList.add(
                    side === "before"
                        ? "edz-drag-over-before"
                        : "edz-drag-over-after",
                );
            }
        },

        onDrop(e) {
            e.preventDefault();
            this.commit(e.target.closest("[data-col-row]"));
        },

        onDragLeave(e) {
            const row = e.target.closest("[data-col-row]");
            if (!row) return;
            row.classList.remove(
                "edz-drag-over-before",
                "edz-drag-over-after",
            );
        },

        onDragEnd() {
            this.reset();
        },

        commit(targetRow) {
            if (!this.draggingKey || !targetRow) {
                this.reset();
                return;
            }

            const rows = [...this.$el.querySelectorAll("[data-col-row]")];
            const isChecked = (r) =>
                r.querySelector("input[type=checkbox]")?.checked;

            const sourceKey = this.draggingKey;
            const targetKey = targetRow.dataset.colKey;

            let order = rows.filter(isChecked).map((r) => r.dataset.colKey);
            const from = order.indexOf(sourceKey);
            if (from === -1) {
                this.reset();
                return;
            }

            order = order.filter((k) => k !== sourceKey);
            let to = order.indexOf(targetKey);
            if (to === -1) {
                to = order.length;
            } else if (this.dropSide === "after") {
                to += 1;
            }
            order.splice(Math.max(0, Math.min(to, order.length)), 0, sourceKey);

            if (this.$wire) {
                this.$wire.reorderDraftColumns(order).catch(() => {});
            }

            this.reset();
        },

        reset() {
            this.draggingKey = null;
            this.overKey = null;
            this.dropSide = "after";
            if (this.$el) {
                this.$el.querySelectorAll("[data-col-row]").forEach((r) => {
                    r.classList.remove(
                        "edz-drag-source",
                        "edz-drag-over-before",
                        "edz-drag-over-after",
                    );
                });
            }
        },
    };
}