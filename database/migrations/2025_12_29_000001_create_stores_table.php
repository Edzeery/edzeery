<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Owner of the store
            $table->foreignUlid('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            // إذا تريد slug فريد على مستوى المنصة كلها (مناسب لدومين/رابط المتجر)
            $table->string('slug')->unique();

            $table->string('logo')->nullable();
            $table->string('cover')->nullable();
            $table->text('description')->nullable();

            $table->enum('status', [
                'active',
                'pending',
                'suspended',
                'closed',
                'draft',
                'blocked',
            ])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);

            // $table->string('slug');
            // $table->unique(['slug', 'deleted_at']);
        });

        Schema::create('store_settings', function (Blueprint $table) {
            $table->foreignUlid('store_id')->primary()->constrained()->cascadeOnDelete();

            $table->string('currency')->default('DZD');
            $table->string('currency_symbol')->default('DA');
            $table->string('language')->default('ar');
            $table->string('timezone')->default('Africa/Algiers');

            $table->boolean('guest_checkout')->default(true);
            $table->boolean('inventory_tracking')->default(true);
            $table->boolean('show_out_of_stock')->default(false);
            $table->boolean('allow_backorder')->default(false);

            $table->json('contact_info')->nullable();

            $table->timestamps();
        });
        Schema::create('store_seo', function (Blueprint $table) {
            $table->foreignUlid('store_id')->primary()->constrained()->cascadeOnDelete();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->string('og_image')->nullable();

            $table->timestamps();
        });

        Schema::create('store_theme_settings', function (Blueprint $table) {
            $table->foreignUlid('store_id')->primary()->constrained()->cascadeOnDelete();

            $table->string('theme')->default('default');
            $table->string('primary_color')->default('#000000');
            $table->string('secondary_color')->default('#ffffff');

            $table->string('font_family')->default('Cairo');

            $table->json('homepage_sections')->nullable(); // hero, featured_products, categories, banners

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_theme_settings');
        Schema::dropIfExists('store_seo');
        Schema::dropIfExists('store_settings');
        Schema::dropIfExists('stores');
    }
};
