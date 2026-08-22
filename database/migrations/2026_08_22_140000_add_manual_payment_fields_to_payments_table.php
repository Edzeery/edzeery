<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('manual_method')->nullable()->after('gateway');
            $table->string('reference_number')->nullable()->after('manual_method');
            $table->string('proof_file_path')->nullable()->after('reference_number');
            $table->foreignUlid('reviewed_by')->nullable()->after('proof_file_path')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('rejection_reason')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'manual_method',
                'reference_number',
                'proof_file_path',
                'reviewed_by',
                'reviewed_at',
                'rejection_reason',
            ]);
        });
    }
};
