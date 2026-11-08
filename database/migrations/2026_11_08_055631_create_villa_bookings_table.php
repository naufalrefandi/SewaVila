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
        Schema::create('villa_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 50)->unique();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name', 150);
            $table->string('customer_email', 150);
            $table->string('customer_phone', 30);
            $table->string('customer_identity_number', 50)->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedInteger('total_nights')->default(1);
            $table->unsignedInteger('guests_count')->default(1);
            $table->text('special_requests')->nullable();
            $table->unsignedBigInteger('price_per_night');
            $table->unsignedBigInteger('total_price');
            $table->unsignedBigInteger('cleaning_fee')->default(0);
            $table->unsignedBigInteger('security_deposit')->default(0);
            $table->unsignedBigInteger('grand_total');
            $table->string('payment_method', 50)->default('qris');
            $table->string('payment_status', 30)->default('pending'); // pending, paid, failed, refunded
            $table->string('booking_status', 30)->default('pending'); // pending, confirmed, checked_in, completed, cancelled
            $table->string('payment_proof')->nullable();
            $table->timestamp('payment_expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'check_in_date', 'check_out_date']);
            $table->index('payment_status');
            $table->index('booking_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villa_bookings');
    }
};
