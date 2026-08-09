<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
};
