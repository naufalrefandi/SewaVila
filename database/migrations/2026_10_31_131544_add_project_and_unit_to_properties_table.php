<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->after('agent_id')->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('province_id')->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->after('city_id')->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->after('district_id')->constrained()->nullOnDelete();

            $table->string('unit_tower')->nullable()->after('building_area'); // e.g. "Tower Orchard", "Tower Ritz"
            $table->string('unit_floor')->nullable()->after('unit_tower'); // e.g. "Lantai 28"
            $table->string('unit_number')->nullable()->after('unit_floor'); // e.g. "Unit 08"
            $table->string('unit_view')->nullable()->after('unit_number'); // e.g. "City View", "Pool View", "Golf View"
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['project_id']);
            $table->dropColumn([
                'province_id',
                'city_id',
                'district_id',
                'project_id',
                'unit_tower',
                'unit_floor',
                'unit_number',
                'unit_view',
            ]);
        });
    }
};
