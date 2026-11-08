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
        Schema::table('agents', function (Blueprint $table) {
            $table->boolean('is_leader')->default(false)->after('is_top_producer');
            $table->foreignId('leader_id')->nullable()->after('is_leader')->constrained('agents')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropForeign(['leader_id']);
            $table->dropColumn(['leader_id', 'is_leader']);
        });
    }
};
