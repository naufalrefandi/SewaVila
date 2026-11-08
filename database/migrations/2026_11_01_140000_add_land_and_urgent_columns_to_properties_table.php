<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Urgent sale / Butuh Laku Cepat (BU) tag
            $table->boolean('is_urgent')->default(false)->after('is_featured');

            // Road access width (e.g. "Row 3 Mobil", "Row 4 Mobil", "Lebar 8 Meter")
            $table->string('road_width')->nullable()->after('facing');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'is_urgent',
                'road_width',
            ]);
        });
    }
};

