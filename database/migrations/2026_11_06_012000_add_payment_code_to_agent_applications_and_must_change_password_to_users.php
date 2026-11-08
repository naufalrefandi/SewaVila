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
        Schema::table('agent_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_applications', 'payment_code')) {
                $table->string('payment_code')->nullable()->unique()->after('status');
            }
            if (!Schema::hasColumn('agent_applications', 'payment_amount')) {
                $table->decimal('payment_amount', 15, 2)->default(0)->after('payment_code');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('agent_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_applications', function (Blueprint $table) {
            if (Schema::hasColumn('agent_applications', 'payment_amount')) {
                $table->dropColumn('payment_amount');
            }
            if (Schema::hasColumn('agent_applications', 'payment_code')) {
                $table->dropUnique(['payment_code']);
                $table->dropColumn('payment_code');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }
        });
    }
};

