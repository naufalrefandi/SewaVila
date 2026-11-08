<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // e.g. "Pakuwon Mall Mansion", "District 8 Senopati", "Greenwich Park BSD"
            $table->string('slug')->unique();
            $table->string('type')->default('apartemen'); // apartemen, kondominium, cluster, ruko_complex
            $table->string('developer')->nullable(); // Pakuwon Group, Sinarmas Land, Agung Sedayu, dll.
            $table->text('address')->nullable();
            $table->integer('total_floors')->nullable();
            $table->integer('year_completed')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('facilities')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
