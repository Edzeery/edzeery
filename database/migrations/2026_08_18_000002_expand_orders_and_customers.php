<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignUlid('user_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUlid('state_id')
                ->nullable()
                ->after('user_id')
                ->constrained('states')
                ->nullOnDelete();

            $table->foreignUlid('city_id')
                ->nullable()
                ->after('state_id')
                ->constrained('cities')
                ->nullOnDelete();

            $table->foreignUlid('stopdesk_point_id')
                ->nullable()
                ->after('city_id')
                ->constrained('stopdesk_points')
                ->nullOnDelete();

            $table->text('address')->nullable()->after('stopdesk_point_id');
            $table->string('delivery_type')->default('home')->after('address');
            $table->string('payment_method')->default('cod')->after('delivery_type');
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('payment_method');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->text('address')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('address');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'user_id',
                'state_id',
                'city_id',
                'stopdesk_point_id',
                'address',
                'delivery_type',
                'payment_method',
                'shipping_cost',
            ]);
        });
    }
};
