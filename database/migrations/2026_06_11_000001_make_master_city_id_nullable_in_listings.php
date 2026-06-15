<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            // Phase 4 intake sends city_name (free text) without a master_city_id.
            // The admin assigns the master city during review. Until then, null is valid.
            $table->unsignedBigInteger('master_city_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedBigInteger('master_city_id')->nullable(false)->change();
        });
    }
};
