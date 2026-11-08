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
        Schema::create('property_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('owner_name');
            $table->string('owner_phone');
            $table->string('owner_email')->nullable();
            $table->string('property_title');
            $table->string('property_type'); // rumah, apartemen, ruko, villa, tanah, gudang
            $table->string('listing_type'); // dijual, disewa
            $table->unsignedBigInteger('expected_price');
            $table->string('city');
            $table->text('address');
            $table->integer('land_area')->nullable();
            $table->integer('building_area')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->string('certificate')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, listed, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_submissions');
    }
};
