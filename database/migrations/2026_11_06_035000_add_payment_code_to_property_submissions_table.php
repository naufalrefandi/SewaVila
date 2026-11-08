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
        Schema::table('property_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('property_submissions', 'payment_code')) {
                $table->string('payment_code')->nullable()->unique()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('property_submissions', 'payment_code')) {
                $table->dropUnique(['payment_code']);
                $table->dropColumn('payment_code');
            }
        });
    }
};

