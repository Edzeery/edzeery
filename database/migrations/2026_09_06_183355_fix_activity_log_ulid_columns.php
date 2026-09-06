<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The original table used nullableMorphs(), which created unsignedBigInteger
     * morph_id columns. User/Order models use Ulid (26-char string) primary keys,
     * so writing a ULID into the integer columns caused "Data truncated".
     * Widen causer_id/subject_id to string(26) while preserving the indexes.
     */
    public function up(): void
    {
        $connection = config('activitylog.database_connection')
            ?: Schema::getConnection()->getName();

        Schema::connection($connection)->table(config('activitylog.table_name'), function (Blueprint $table) {
            $table->dropIndex('causer');
            $table->dropIndex('subject');
        });

        Schema::connection($connection)->table(config('activitylog.table_name'), function (Blueprint $table) {
            $table->string('causer_id', 26)->nullable()->change();
            $table->string('subject_id', 26)->nullable()->change();
        });

        Schema::connection($connection)->table(config('activitylog.table_name'), function (Blueprint $table) {
            $table->index(['causer_type', 'causer_id'], 'causer');
            $table->index(['subject_type', 'subject_id'], 'subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('activitylog.database_connection')
            ?: Schema::getConnection()->getName();

        Schema::connection($connection)->table(config('activitylog.table_name'), function (Blueprint $table) {
            $table->dropIndex('causer');
            $table->dropIndex('subject');
        });

        Schema::connection($connection)->table(config('activitylog.table_name'), function (Blueprint $table) {
            $table->unsignedBigInteger('causer_id')->nullable()->change();
            $table->unsignedBigInteger('subject_id')->nullable()->change();
        });

        Schema::connection($connection)->table(config('activitylog.table_name'), function (Blueprint $table) {
            $table->index(['causer_type', 'causer_id'], 'causer');
            $table->index(['subject_type', 'subject_id'], 'subject');
        });
    }
};