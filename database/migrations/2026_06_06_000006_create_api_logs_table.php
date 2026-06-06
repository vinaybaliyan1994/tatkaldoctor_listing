<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('api_key', 64)->nullable()->index();
            $table->string('endpoint', 500);
            $table->string('method', 10);
            $table->string('request_ip', 45)->nullable();
            $table->json('request_headers')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->boolean('success')->default(false)->index();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
