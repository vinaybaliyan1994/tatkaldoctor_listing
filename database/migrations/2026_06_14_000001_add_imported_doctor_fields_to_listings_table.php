<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->boolean('is_imported')->default(false)->after('source');
            $table->boolean('is_verified_by_tatkaldoctor')->default(false)->after('is_imported');
            $table->string('external_source', 100)->nullable()->after('is_verified_by_tatkaldoctor');
            $table->text('external_url')->nullable()->after('external_source');
        });

        DB::table('listings')
            ->where('verification_status', 'approved')
            ->where(function ($query): void {
                $query->whereNull('source')
                    ->orWhere('source', '!=', 'google_business_import');
            })
            ->update(['is_verified_by_tatkaldoctor' => true]);
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'is_imported',
                'is_verified_by_tatkaldoctor',
                'external_source',
                'external_url',
            ]);
        });
    }
};
