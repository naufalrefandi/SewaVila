<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_applications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('city');
            $table->string('office_branch_preference')->nullable();
            $table->string('experience_level')->default('baru'); // baru, 1-3_tahun, diatas_3_tahun
            $table->string('license_number')->nullable(); // No. Lisensi AREBI jika ada
            $table->string('photo')->nullable();
            $table->text('motivation_bio')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_applications');
    }
};
