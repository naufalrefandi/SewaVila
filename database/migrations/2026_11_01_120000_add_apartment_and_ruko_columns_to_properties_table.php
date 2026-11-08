<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Apartment Unit Specifics
            $table->string('unit_type')->nullable()->after('property_type'); // Studio, 1 BR, 2 BR, 3 BR, Penthouse, Ruko Komersial, etc.
            $table->string('dimension')->nullable()->after('land_area'); // e.g. "4 x 15 m", "5 x 15 m"

            // Dual Pricing & Detailed Rental Options
            $table->unsignedBigInteger('sale_price')->nullable()->after('price'); // Specific sale price when dual listing
            $table->unsignedBigInteger('rent_price')->nullable()->after('sale_price'); // Specific rent price when dual listing
            $table->unsignedBigInteger('monthly_rent_price')->nullable()->after('rent_price'); // e.g. Rp 4.000.000 / bulan
            $table->unsignedBigInteger('original_price')->nullable()->after('monthly_rent_price'); // Strikethrough price e.g. Rp 3.000.000.000 before dropping to Rp 2.000.000.000

            // Operational & Transaction Notes
            $table->string('ipl_info')->nullable()->after('price_prefix'); // e.g. "Non IPL", "Include IPL", "IPL Rp 350.000/bln"
            $table->string('transfer_fee_info')->nullable()->after('ipl_info'); // e.g. "Non Peralihan (Ditanggung Pembeli)", "Free Biaya Peralihan"

            // Allow decimal for building_area and land_area
            $table->decimal('building_area', 10, 2)->nullable()->change();
            $table->decimal('land_area', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'unit_type',
                'dimension',
                'sale_price',
                'rent_price',
                'monthly_rent_price',
                'original_price',
                'ipl_info',
                'transfer_fee_info',
            ]);
            $table->integer('building_area')->nullable()->change();
            $table->integer('land_area')->nullable()->change();
        });
    }
};

