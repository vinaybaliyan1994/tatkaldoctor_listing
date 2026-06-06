<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('qr_slug', 191)->nullable()->unique()->after('rejection_reason');
            $table->string('public_profile_url', 255)->nullable()->after('qr_slug');
            $table->string('qr_code_path', 255)->nullable()->after('public_profile_url');
            $table->timestamp('qr_generated_at')->nullable()->after('qr_code_path');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex(['qr_slug']);
            $table->dropColumn(['qr_slug', 'public_profile_url', 'qr_code_path', 'qr_generated_at']);
        });
    }
};
