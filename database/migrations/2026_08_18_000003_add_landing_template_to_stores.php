<?php

use App\Enums\Store\LandingTemplateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('landing_template')
                ->default(LandingTemplateEnum::SINGLE_PRODUCT->value)
                ->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('landing_template');
        });
    }
};
