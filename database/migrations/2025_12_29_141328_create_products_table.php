<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * PRODUCTS
         * المنتج الأساسي (وصفي)
         */
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignUlid('brand_id')
                ->nullable()
                ->constrained('brands')
                ->nullOnDelete();

            $table->foreignUlid('primary_category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            // Basic info
            $table->string('name');
            $table->string('slug');
            $table->string('sku')->unique(); // Base SKU
            $table->string('barcode')->nullable();

            // Product type
            $table->enum('type', ['simple', 'variable'])->default('variable');

            // Descriptions
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            // Pricing
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();

            // Unit
            $table->string('unit')->default('piece');

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Display & status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            // Indexes & constraints
            $table->unique(['store_id', 'sku']);
            $table->unique(['store_id', 'slug']);
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('price');
        });


        /**
         * PRODUCT VARIANTS
         * SKU الحقيقي القابل للبيع
         */
        Schema::create('product_variants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained()->cascadeOnDelete();

            $table->foreignUlid('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('name');

            // Identification
            $table->string('sku');
            $table->string('barcode')->nullable();

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();

            // Inventory snapshot (ledger هو المصدر الحقيقي)
            $table->integer('stock')->default(0);

            // Low stock alerts
            $table->unsignedInteger('low_stock_threshold')->default(0);
            $table->timestamp('last_low_stock_notified_at')->nullable();

            // Shipping
            $table->decimal('weight', 8, 3)->nullable(); // kg
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->unique(['store_id', 'sku']);
            $table->unique(['store_id', 'barcode']); // يسمح بتكرار NULL، ويمنع تكرار barcode غير NULL داخل المتجر
        });

        /**
         * PRODUCT OPTIONS
         * مثال: Color, Size
         */
        Schema::create('product_options', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('type')->default('text'); // text, color, size...
            $table->integer('sort_order')->default(0);
            $table->unique(['store_id', 'name']);
            $table->timestamps();
        });

        /**
         * PRODUCT OPTION VALUES
         * مثال: Red, Black | 40, 42
         */
        Schema::create('product_option_values', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained()->cascadeOnDelete();

            $table->foreignUlid('product_option_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('value');
            $table->string('hex_color')->nullable(); // For color options
            $table->integer('sort_order')->default(0);
            $table->unique(['store_id', 'value']);
            $table->timestamps();
        });

        /**
         * VARIANT OPTION VALUES PIVOT
         * يربط كل Variant بقيمه
         */
        Schema::create('product_variant_option_value', function (Blueprint $table) {
            $table->id();

            $table->foreignUlid('product_variant_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();

            $table->foreignUlid('product_option_value_id')
                ->constrained('product_option_values')
                ->cascadeOnDelete();

            $table->foreignUlid('product_option_id')
                ->constrained('product_options')
                ->cascadeOnDelete();

            $table->unique(
                ['product_variant_id', 'product_option_value_id'],
                'pv_pov_unique'
            );
        });

        /**
         * PRODUCT IMAGES
         * صور للمنتج أو للـ Variant
         */
        Schema::create('product_images', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->morphs('imageable'); // Product | ProductVariant

            $table->string('path');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);

            $table->index(['store_id', 'imageable_type', 'imageable_id']);
            $table->timestamps();
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUlid('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUlid('category_id')
                ->constrained()
                ->cascadeOnDelete();

            // (اختياري) ترتيب المنتج داخل التصنيف
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            // يمنع تكرار نفس التصنيف لنفس المنتج داخل نفس المتجر
            $table->unique(['store_id', 'product_id', 'category_id'], 'cat_prod_unique');

            // فهارس مفيدة للاستعلامات
            $table->index(['store_id', 'category_id']);
            $table->index(['store_id', 'product_id']);
        });
    }

    public function down(): void
    {

        Schema::dropIfExists('category_product');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_variant_option_value');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
