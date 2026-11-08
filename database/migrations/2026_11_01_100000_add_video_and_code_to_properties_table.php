<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('property_code')->nullable()->after('slug');
            $table->string('video')->nullable()->after('images');
            $table->string('owner_name')->nullable()->after('agent_id');
            $table->string('owner_phone')->nullable()->after('owner_name');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['property_code', 'video', 'owner_name', 'owner_phone']);
        });
    }
};

