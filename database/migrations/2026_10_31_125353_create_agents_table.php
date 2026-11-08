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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('photo')->nullable();
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('agency_name')->default('PropNex Indonesia');
            $table->string('office_branch')->default('Jakarta CBD Office');
            $table->string('position')->default('Senior Property Consultant');
            $table->integer('experience_years')->default(5);
            $table->decimal('rating', 2, 1)->default(4.9);
            $table->integer('active_listings_count')->default(0);
            $table->text('bio')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->boolean('is_top_producer')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
