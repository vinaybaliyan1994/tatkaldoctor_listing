<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->char('country_code', 3);
            $table->foreign('country_code')->references('code')->on('master_countries')->restrictOnDelete();

            $table->unsignedBigInteger('master_city_id');
            $table->foreign('master_city_id')->references('id')->on('master_cities')->restrictOnDelete();

            $table->unsignedBigInteger('master_location_id')->nullable();
            $table->foreign('master_location_id')->references('id')->on('master_locations')->nullOnDelete();

            $table->string('name', 191);
            $table->string('hospital_name', 191)->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->string('personal_contact_no', 20)->nullable();
            $table->string('appointment_no', 20)->nullable();
            $table->json('qualifications')->nullable();
            $table->json('services')->nullable();
            $table->json('meta_data')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
