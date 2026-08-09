# database\migrations\0001_01_01_000001_create_locations_table.php

public function up(): void
{
/_
|--------------------------------------------------------------------------
| Countries
|--------------------------------------------------------------------------
_/
Schema::create('countries', function (Blueprint $table) {
$table->ulid('id')->primary();

            $table->string('name');
            $table->string('arabic_name')->nullable();

            $table->string('code', 5)->unique();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_cod_available')->default(true);

            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | States (Wilayas)
        |--------------------------------------------------------------------------
        */
        Schema::create('states', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('country_id')
                ->constrained()
                ->cascadeOnDelete();

            // الرمز الإداري الرسمي 01 → 58
            $table->char('state_code', 2)->index();

            $table->string('name');
            $table->string('arabic_name')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_cod_available')->default(true);

            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->decimal('longitude', 9, 6)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();

            $table->timestamps();

            $table->unique(['country_id', 'state_code']);
        });

        /*
        |--------------------------------------------------------------------------
        | Cities
        |--------------------------------------------------------------------------
        */
        Schema::create('cities', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('state_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->string('arabic_name')->nullable();

            // اختياري لو احتجت كود بلدية لاحقًا
            $table->string('city_code', 10)->nullable()->index();
            $table->string('post_code');

            $table->boolean('is_active')->default(true);
            $table->boolean('is_cod_available')->default(true);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->decimal('longitude', 9, 6)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->timestamps();

            $table->unique(['state_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }

# database\migrations\0001_01_01_000002_create_users_table.php

    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // لاحقًا عندك migration update_users_table لإضافة country/state/city
            // لا نضيفها هنا لتفادي مشاكل ترتيب الـ FK

            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');
            $table->text('app_authentication_secret')->nullable();

            $table->foreignUlid('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();

            $table->foreignUlid('state_id')
                ->nullable()
                ->constrained('states')
                ->nullOnDelete();

            $table->foreignUlid('city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
            // اختياري لكن مُستحسن للأداء إذا عندك فلترة كثيرة حسب الموقع:
            $table->index(['country_id', 'state_id', 'city_id'], 'users_location_idx');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->foreignUlid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->index();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('user_settings', function (Blueprint $table) {
            $table->foreignUlid('user_id')
                ->primary()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->json('preferences')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }

# database\migrations\0001_01_01_000003_create_cache_table.php

public function up(): void
{
Schema::create('cache', function (Blueprint $table) {
$table->string('key')->primary();
$table->mediumText('value');
$table->integer('expiration');
});

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }

# database\migrations\0001_01_01_000004_create_jobs_table.php

    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
    }

# database\migrations\0001_01_01_000005_create_profiles_table.php

public function up(): void
    {

        Schema::create('profiles', function (Blueprint $table) {
            $table->foreignUlid('user_id')
                ->primary()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('profile_picture')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
# database\migrations\2025_09_21_192922_create_settings_table.php
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
# database\migrations\2025_12_29_000001_create_stores_table.php

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

# database\migrations\2025_12_29_000003_create_store_memberships_table.php

    public function up(): void
    {
        Schema::create('store_memberships', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('invited_by')->nullable()->constrained('users')->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->date('invited_at')->nullable();
            $table->date('accepted_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
            $table->unique(['store_id', 'user_id', 'deleted_at']);
            $table->index(['user_id', 'is_active']);
            $table->index(['store_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_memberships');
    }
    
# database\migrations\2025_12_29_120438_create_permission_tables.php

public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        throw_if(empty($tableNames), Exception::class, 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        throw_if($teams && empty($columnNames['team_foreign_key'] ?? null), Exception::class, 'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        Schema::create($tableNames['permissions'], static function (Blueprint $table) {
            // $table->engine('InnoDB');
            $table->bigIncrements('id'); // permission id
            $table->string('name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string('guard_name'); // For MyISAM use string('guard_name', 25);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
            // $table->engine('InnoDB');
            $table->bigIncrements('id'); // role id
            if ($teams || config('permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string('guard_name'); // For MyISAM use string('guard_name', 25);
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger($pivotPermission);

            $table->string('model_type');
            $table->char($columnNames['model_morph_key'], 26);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary(
                    [$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            }
        });

        Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole);

            $table->string('model_type');
            $table->char($columnNames['model_morph_key'], 26);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary(
                    [$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            }
        });

        Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        throw_if(empty($tableNames), Exception::class, 'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }

# database\migrations\2025_12_29_141326_create_brands_table.php

 public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
           $table->ulid('id')->primary();

            // NULL = system template brand
            $table->foreignUlid('store_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();



            $table->string('name');
            $table->string('slug');
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();
            $table->unique(['store_id', 'deleted_at', 'slug'], 'brands_scope_slug_unique');
             $table->index(['store_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }

# database\migrations\2025_12_29_141327_create_categories_table.php

    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // nullable → system category
            $table->foreignUlid('store_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignUlid('parent_id')->nullable()->constrained('categories')->nullOnDelete();

            // // scope: system = 0, store = store_id
            // $table->unsignedBigInteger('store_scope_id')->virtualAs(DB::raw('IFNULL(store_id, 0)'));

            $table->string('name');
            $table->string('slug');
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();

            // فريد داخل الـ scope
            $table->unique(['store_id', 'deleted_at', 'slug'], 'categories_scope_slug_unique');

            $table->index(['store_id', 'parent_id']);
            $table->index(['store_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }

# database\migrations\2025_12_29_141328_create_products_table.php

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

# database\migrations\2025_12_29_144149_create_statuses_table.php

    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // null = system status
            $table->foreignUlid('store_id')
                ->nullable()
                ->constrained()->nullOnDelete();

            /**
             * نطاق موحد:
             * system = 0
             * store  = store_id
             */
            $table->unsignedBigInteger('store_scope_id')
                ->virtualAs(DB::raw('IFNULL(store_id, 0)'));

            // order | payment | shipment
            $table->string('type');

            // pending | paid | shipped | custom_hold
            $table->string('key');

            // الاسم المعروض
            $table->string('label');

            $table->string('color')->default('gray');

            // حالة نظامية غير قابلة للحذف
            $table->boolean('is_system')->default(false);

            // هل تؤثر على المخزون؟
            $table->boolean('affects_inventory')->default(false);

            // SALE | RETURN | RESERVE | RELEASE | ADJUSTMENT
            $table->string('movement_type')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            /**
             * يضمن:
             * - system status فريد
             * - store status فريد داخل نفس المتجر
             */
            $table->unique(
                ['store_scope_id', 'type', 'key'],
                'statuses_scope_type_key_unique'
            );
            $table->unique(['store_id', 'type', 'key']);

            $table->index(['type', 'is_system']);
            $table->index(['store_id', 'type']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }

# database\migrations\2025_12_29_144151_create_customers_table.php

    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();
            $table->boolean('status')->default(true);

            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();

            $table->foreignUlid('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();

            $table->foreignUlid('state_id')
                ->nullable()
                ->constrained('states')
                ->nullOnDelete();

            $table->foreignUlid('city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();

            $table->unique(['store_id', 'phone']);
            // اختياري لكن مُستحسن للأداء إذا عندك فلترة كثيرة حسب الموقع:
            $table->index(['country_id', 'state_id', 'city_id'], 'customers_location_idx');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }

# database\migrations\2025_12_29_144152_create_orders_table.php

    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignUlid('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->foreignUlid('created_by_membership_id')
                ->nullable()
                ->constrained('store_memberships')
                ->nullOnDelete();

            $table->foreignUlid('status_id')
                ->nullable()
                ->constrained('statuses')
                ->nullOnDelete();

            $table->string('number');

            $table->decimal('total_amount', 10, 2)->default(0);

            $table->string('phone_secondary')->nullable();
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('deleted_at');
            // رقم الطلب فريد داخل نفس المتجر

            $table->unique(['store_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }

# database\migrations\2025_12_29_144153_create_order_items_table.php

    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignUlid('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignUlid('product_variant_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();

            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->softDeletes();
            $table->index('deleted_at');
            $table->timestamps();

            $table->unique(['order_id', 'product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }

# database\migrations\2026_01_05_125049_create_inventory_movements_table.php

    /\*\*
    _ Run the migrations.
    _/
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
           $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignUlid('product_variant_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();

            $table->foreignUlid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // ALWAYS positive, direction determined by type
            $table->unsignedInteger('quantity');

            // Snapshot after movement
            $table->integer('balance_after');

            // InventoryMovementType enum value
            $table->string('type');

            // Order, Purchase, Adjustment, etc.
            $table->nullableMorphs('source');

            $table->timestamps();
            $table->softDeletes();
            $table->index(
                ['store_id', 'product_variant_id', 'deleted_at', 'created_at', 'type'],
                'im_Sid_PVid_DelAt_CAt_type_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }

# database\migrations\2026_01_08_225323_create_shipping_providers_table.php

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipping_providers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->string('name'); // Aramex, DHL, Local
            $table->json('credentials');
            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_providers');
    }

# database\migrations\2026_01_11_044202_create_notifications_table.php

     public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
           $table->uuid('id')->primary();
            $table->foreignUlid('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignUlid('store_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('type');

            $table->ulidMorphs('notifiable');

            $table->json('data');

            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }

# database\migrations\2026_01_14_120433_create_plans_table.php

    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique(); // products_limit, staff_limit
            $table->string('type')->default('number'); // number, boolean
            $table->string('unit')->nullable(); // products, users
            $table->text('description')->nullable();

            $table->boolean('consumable')->default(false);
            $table->boolean('quota')->default(false);
            $table->unsignedInteger('periodicity')->nullable();
            $table->string('periodicity_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('plans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->integer('trial_days')->default(0); // ✔️ هنا مكانها
            $table->integer('max_stores')->default(1); // ✔️ multi-store

            $table->foreignUlid('upgrade_to_plan_id')->nullable()
                ->constrained('plans')
                ->nullOnDelete(); // ✔️ ترقية تلقائية

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('currency', 10)->default('DZD');
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('plan_prices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('plan_id')->constrained('plans')->cascadeOnDelete();

            $table->enum('billing_period', ['monthly', 'yearly']);
            $table->decimal('price', 10, 2);
            $table->string('currency', 10)->default('DZD');
            $table->integer('duration')->comment('days'); // 30 / 365

            $table->timestamps();
            $table->boolean('is_active')->default(true);
            $table->unique(['plan_id', 'billing_period']);
        });

        Schema::create('plan_plan_feature', function (Blueprint $table) {
            $table->id('id');
            $table->foreignUlid('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignUlid('plan_feature_id')->constrained('plan_features')->cascadeOnDelete();

            $table->string('value')->nullable(); // 100, unlimited, true
            $table->decimal('charges')->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'plan_feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_plan_feature');

        Schema::dropIfExists('plan_prices');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('plan_features');
    }

# database\migrations\2026_01_14_120734_create_subscriptions_table.php

    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignUlid('plan_id')
                ->nullable()
                ->constrained('plans')
                ->nullOnDelete();

            $table->foreignUlid('plan_price_id')
                ->constrained('plan_prices')
                ->cascadeOnDelete();

            $table->boolean('was_switched')->default(false);
            $table->boolean('is_trial')->default(false);

            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('suppressed_at')->nullable();

            $table->timestamp('grace_ends_at')->nullable();

            $table->enum('status', [
                'active',
                'pending',
                'expired',
                'canceled',
                'suspended',
            ])->default('pending');

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('ends_at');
        });

        Schema::create('subscription_renewals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->boolean('overdue');
            $table->boolean('renewal');
            $table->foreignUlid('subscription_id')
                ->constrained('subscriptions')
                ->cascadeOnDelete();
            $table->timestamps();
        });


        Schema::create('feature_consumptions', function (Blueprint $table) {
            $table->id();

            $table->foreignUlid('subscription_id')
                ->constrained('subscriptions')
                ->cascadeOnDelete();

            $table->Decimal('consumption')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->foreignUlid('plan_feature_id')->constrained('plan_features')->cascadeOnDelete();

            $table->unique(['subscription_id', 'plan_feature_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_consumptions');
        Schema::dropIfExists('subscription_renewals');
        Schema::dropIfExists('subscriptions');
    }

# database\migrations\2026_01_14_120810_create_payments_table.php

    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUlid('store_id')
                ->nullable()
                ->constrained('stores')
                ->nullOnDelete();

            $table->foreignUlid('subscription_id')
                ->nullable()
                ->constrained('subscriptions')
                ->nullOnDelete();

            $table->foreignUlid('plan_price_id')
                ->constrained('plan_prices')
                ->cascadeOnDelete();

            $table->string('gateway')->default('chargily');
            $table->string('transaction_id')->nullable();

            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'refunded',
                'canceled',
            ])->default('pending');

            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('DZD');

            $table->json('meta')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }

# database\migrations\2026_01_16_093656_create_imports_table.php

    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table): void {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->nullable()
                ->constrained('stores')
                ->nullOnDelete();

            $table->timestamp('completed_at')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('importer');
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('total_rows');
            $table->unsignedInteger('successful_rows')->default(0);

            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imports');
    }

# database\migrations\2026_01_16_093657_create_exports_table.php

    public function up(): void
    {
        Schema::create('exports', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')
                ->nullable()
                ->constrained('stores')
                ->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->string('file_disk');
            $table->string('file_name')->nullable();
            $table->string('exporter');
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('total_rows');
            $table->unsignedInteger('successful_rows')->default(0);
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exports');
    }

# database\migrations\2026_01_16_093658_create_failed_import_rows_table.php

    public function up(): void
    {
        Schema::create('failed_import_rows', function (Blueprint $table): void {
             $table->ulid('id')->primary();
            $table->foreignUlid('store_id')
                ->nullable()
                ->constrained('stores')
                ->nullOnDelete();
            $table->json('data');
            $table->foreignUlid('import_id')->constrained('imports')->cascadeOnDelete();
            $table->text('validation_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_import_rows');
    }
 

# database\migrations\2026_01_26_185036_create_store_status_histories_table.php

    public function up(): void
    {
        Schema::create('store_status_histories', function (Blueprint $table) {
             $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->enum('status', [
                'active',
                'pending',
                'suspended',
                'closed',
                'draft',
                'blocked',
                'approved',
                'rejected',
            ]);

            $table->text('reason')->nullable();
            $table->foreignUlid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_status_histories');
    }

# database\migrations\2026_01_26_185137_create_store_user_requests_table.php

    public function up(): void
    {
        Schema::create('store_user_requests', function (Blueprint $table) {
             $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'rejected'])->default('pending');
            $table->foreignUlid('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_user_requests');
    }

# database\migrations\2026_02_14_162829_create_templates_table.php

    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('preview_image')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }

# database\migrations\2026_02_15_154438_create_invoices_table.php

    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->unique();

            // Company information
            $table->string('company_name');
            $table->text('company_address')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_logo')->nullable();

            // Client information
            $table->string('client_name');
            $table->text('client_address')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();

            // Invoice details
            $table->date('invoice_date');
            $table->date('due_date');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            // Calculations
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            // Template and status
            $table->foreignUlid('template_id')->nullable()->constrained('templates')->nullOnDelete();
            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled'])->default('draft');

            $table->softDeletes();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }

# database\migrations\2026_02_15_162111_create_invoice_items_table.php

    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid(column: 'invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }

# database\migrations\2026_02_16_112126_add_pdf_path_to_invoices_table.php
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });
    }

# database\migrations\2026_02_22_220740_create_order_status_histories_table.php

    public function up(): void
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignUlid('status_id')
                ->constrained('statuses')
                ->restrictOnDelete();

            $table->foreignUlid('changed_by_membership_id')
                ->nullable()
                ->constrained('store_memberships')
                ->nullOnDelete();

            $table->text('reason')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
