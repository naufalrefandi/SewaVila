<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('property_type'); // rumah, apartemen, ruko, villa, tanah, gudang
            $table->string('listing_type'); // dijual, disewa
            $table->unsignedBigInteger('price');
            $table->string('price_prefix')->nullable(); // Nego, Mulai dari
            $table->string('rent_period')->nullable(); // tahun, bulan
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('land_area')->nullable(); // m2
            $table->integer('building_area')->nullable(); // m2
            $table->integer('carports')->nullable();
            $table->integer('floors')->default(1);
            $table->string('certificate')->nullable(); // SHM, HGB, Strata Title, Girik
            $table->integer('electricity')->nullable(); // Watt / VA
            $table->string('facing')->nullable(); // Utara, Selatan, Timur, Barat
            $table->string('furnished_status')->default('unfurnished'); // unfurnished, semi-furnished, fully-furnished
            $table->integer('year_built')->nullable();
            $table->string('province')->default('DKI Jakarta');
            $table->string('city'); // Jakarta Selatan, Tangerang Selatan, dll.
            $table->string('district'); // Kebayoran Baru, Serpong, dll.
            $table->text('address');
            $table->string('map_coordinate')->nullable();
            $table->longText('description');
            $table->json('images');
            $table->json('facilities')->nullable();
            $table->json('nearby_points')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new_launch')->default(false);
            $table->boolean('is_verified')->default(true);
            $table->string('status')->default('active'); // active, sold, rented, pending
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
