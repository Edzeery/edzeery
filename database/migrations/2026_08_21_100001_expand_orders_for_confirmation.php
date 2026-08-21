<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignUlid('assigned_to_membership_id')
                ->nullable()
                ->after('created_by_membership_id')
                ->constrained('store_memberships')
                ->nullOnDelete();

            $table->timestamp('assigned_at')->nullable()->after('assigned_to_membership_id');
            $table->string('assignment_method')->nullable()->after('assigned_at');
            $table->foreignUlid('assigned_by_membership_id')
                ->nullable()
                ->after('assignment_method')
                ->constrained('store_memberships')
                ->nullOnDelete();

            $table->unsignedInteger('confirmation_attempts')->default(0)->after('assigned_by_membership_id');
            $table->timestamp('last_contact_at')->nullable()->after('confirmation_attempts');
            $table->decimal('weight_kg', 6, 2)->nullable()->after('last_contact_at');
            $table->string('shipment_type')->default('delivery')->after('weight_kg');

            $table->index('assigned_to_membership_id');
            $table->index(['store_id', 'status_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignUlid('product_id')
                ->nullable()
                ->after('product_variant_id')
                ->constrained('products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignUuid('product_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'status_id']);
            $table->dropIndex(['assigned_to_membership_id']);
            $table->dropColumn([
                'assigned_to_membership_id', 'assigned_at', 'assignment_method',
                'assigned_by_membership_id', 'confirmation_attempts',
                'last_contact_at', 'weight_kg', 'shipment_type',
            ]);
        });
    }
};
