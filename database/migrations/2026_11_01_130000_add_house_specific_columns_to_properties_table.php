<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Maid room specification (support 3+1 KT, 3+1 KM)
            $table->unsignedSmallInteger('maid_bedrooms')->nullable()->after('bedrooms');
            $table->unsignedSmallInteger('maid_bathrooms')->nullable()->after('bathrooms');

            // Flooring & Premium Material Finishings (e.g. Full Marmer, Granit, Parket)
            $table->string('flooring_material')->nullable()->after('furnished_status');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'maid_bedrooms',
                'maid_bathrooms',
                'flooring_material',
            ]);
        });
    }
};

