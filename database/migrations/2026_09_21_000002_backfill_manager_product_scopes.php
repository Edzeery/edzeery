<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * One-off backfill: copy the MANAGER rows of `confirmation_product_assignments`
 * into the dedicated `membership_product_scopes` visibility table.
 *
 * Non-destructive by decision: nothing is moved, edited or deleted. A manager
 * row is genuinely ambiguous — it may have been created as a specialist
 * assignment (Phase 34.1 UI) or as a visibility scope (Phase 36.3 UI), possibly
 * both — so the copy is kept as scope while the original stays as the
 * specialist row until the store owner resolves it in the teams screen.
 * Staff rows are unambiguous specialist rows and are never copied.
 *
 * Idempotent: the (store_id, membership_id, product_id) unique index makes the
 * insert ignore anything already present, so reruns are safe no-ops.
 */
return new class extends Migration
{
    public function up(): void
    {
        $copied = 0;
        $stores = [];

        $this->sourceQuery()
            ->orderBy('confirmation_product_assignments.membership_id')
            ->orderBy('confirmation_product_assignments.product_id')
            ->chunk(500, function ($rows) use (&$copied, &$stores) {
                $payload = [];
                $now = now();

                foreach ($rows as $row) {
                    $payload[] = [
                        'id' => (string) Str::ulid(),
                        'store_id' => $row->store_id,
                        'membership_id' => $row->membership_id,
                        'product_id' => $row->product_id,
                        'created_by_membership_id' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $inserted = DB::table('membership_product_scopes')->insertOrIgnore($payload);

                if ($inserted > 0) {
                    $copied += $inserted;

                    foreach ($payload as $row) {
                        $stores[$row['store_id']] = true;
                    }
                }
            });

        Log::info('Membership product scope backfill completed', [
            'rows_copied' => $copied,
            'stores_affected' => count($stores),
            'source' => 'confirmation_product_assignments (manager rows only)',
        ]);

        if ($copied === 0) {
            $this->note('No manager rows found in confirmation_product_assignments — nothing to copy.');
        } else {
            $this->note(sprintf(
                'Copied %d manager row(s) across %d store(s) into membership_product_scopes. '
                . 'The originals remain in confirmation_product_assignments and stay pending owner '
                . 'review from the teams product-scope screen.',
                $copied,
                count($stores)
            ));
        }
    }

    public function down(): void
    {
        // Intentionally empty: the copied rows are live visibility scopes now.
        // Rolling back would either drop real scoping or resurrect the ambiguity
        // in the specialist table — both worse than leaving the copies in place.
    }

    /**
     * Manager-role rows only, with the membership's store checked against the
     * row's store so a mismatched legacy row can never seed a cross-store scope.
     */
    private function sourceQuery()
    {
        return DB::table('confirmation_product_assignments')
            ->join('store_memberships', 'store_memberships.id', '=', 'confirmation_product_assignments.membership_id')
            ->where('store_memberships.role', 'manager')
            ->whereColumn('store_memberships.store_id', 'confirmation_product_assignments.store_id')
            ->select([
                'confirmation_product_assignments.store_id',
                'confirmation_product_assignments.membership_id',
                'confirmation_product_assignments.product_id',
            ]);
    }

    private function note(string $message): void
    {
        if (isset($this->output) && $this->output) {
            $this->output->writeln($message);
        }
    }
};
