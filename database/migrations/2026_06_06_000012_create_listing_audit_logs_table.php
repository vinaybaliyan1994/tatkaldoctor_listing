<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('action', 100);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('changed_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['listing_id', 'action']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('listing_audit_logs', function (Blueprint $table) {
            $table->dropForeign(['changed_by']);
        });
        Schema::dropIfExists('listing_audit_logs');
    }
};
