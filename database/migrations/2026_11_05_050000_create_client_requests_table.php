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
        Schema::create('client_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->string('client_name');
            $table->string('client_phone');
            $table->string('client_email')->nullable();
            $table->string('listing_type')->default('dijual'); // dijual, disewa, semua
            $table->string('property_type')->nullable(); // rumah, apartemen, ruko, tanah, gudang, dll
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('city')->nullable();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->string('district')->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->unsignedBigInteger('min_price')->nullable();
            $table->unsignedBigInteger('max_price')->nullable();
            $table->integer('min_bedrooms')->nullable();
            $table->integer('min_bathrooms')->nullable();
            $table->decimal('min_land_area', 10, 2)->nullable();
            $table->decimal('min_building_area', 10, 2)->nullable();
            $table->string('furnishing')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active'); // active, dealt, cancelled
            $table->timestamp('last_followed_up_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_requests');
    }
};

