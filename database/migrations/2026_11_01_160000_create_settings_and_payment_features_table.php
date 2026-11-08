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
        // 1. Settings Table
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique()->index();
                $table->text('value')->nullable();
                $table->string('group')->default('general')->index();
                $table->timestamps();
            });
        }

        // 2. Add Payment Columns to property_submissions
        Schema::table('property_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('property_submissions', 'fee_amount')) {
                $table->decimal('fee_amount', 14, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('property_submissions', 'payment_status')) {
                $table->string('payment_status')->default('unpaid')->after('fee_amount'); // unpaid, pending, paid
            }
            if (!Schema::hasColumn('property_submissions', 'payment_proof')) {
                $table->string('payment_proof')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('property_submissions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_proof');
            }
        });

        // 3. Add listing_quota to agents
        Schema::table('agents', function (Blueprint $table) {
            if (!Schema::hasColumn('agents', 'listing_quota')) {
                $table->integer('listing_quota')->default(3)->after('active_listings_count');
            }
        });

        // 4. Create agent_transactions table
        if (!Schema::hasTable('agent_transactions')) {
            Schema::create('agent_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
                $table->string('type')->index(); // 'quota_topup', 'featured_listing'
                $table->decimal('amount', 14, 2);
                $table->integer('quota_added')->nullable();
                $table->integer('duration_days')->nullable();
                $table->string('payment_proof')->nullable();
                $table->string('status')->default('pending')->index(); // 'pending', 'approved', 'rejected'
                $table->text('notes')->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_transactions');

        Schema::table('agents', function (Blueprint $table) {
            if (Schema::hasColumn('agents', 'listing_quota')) {
                $table->dropColumn('listing_quota');
            }
        });

        Schema::table('property_submissions', function (Blueprint $table) {
            $cols = ['fee_amount', 'payment_status', 'payment_proof', 'paid_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('property_submissions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('settings');
    }
};

