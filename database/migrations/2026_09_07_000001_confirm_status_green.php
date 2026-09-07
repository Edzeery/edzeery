<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('statuses')
            ->where('type', 'order')
            ->where('key', 'confirmed')
            ->where('is_system', true)
            ->update(['color' => 'success']);
    }

    public function down(): void
    {
        DB::table('statuses')
            ->where('type', 'order')
            ->where('key', 'confirmed')
            ->where('is_system', true)
            ->update(['color' => 'info']);
    }
};