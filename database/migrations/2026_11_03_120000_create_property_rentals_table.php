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
        Schema::create('property_rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Tenant Identity
            $table->string('tenant_name');
            $table->string('tenant_phone');
            $table->string('tenant_email')->nullable();
            $table->string('tenant_id_card')->nullable(); // No KTP / Paspor
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('tenant_address')->nullable();

            // Lease Duration & Period ("sampai kapan")
            $table->date('start_date');
            $table->date('end_date'); // Tanggal berakhir sewa
            $table->string('period_type')->default('tahun'); // tahun, bulan, hari
            $table->integer('duration')->default(1);

            // Financial & Deposit ("termasuk deposit")
            $table->decimal('rent_price', 15, 2);
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->string('deposit_status')->default('held'); // held, refunded, partially_refunded, deducted
            $table->decimal('deposit_refunded_amount', 15, 2)->nullable();
            $table->date('deposit_refunded_at')->nullable();
            $table->text('deposit_notes')->nullable();
            $table->string('payment_status')->default('paid'); // paid, partial, unpaid

            // Documents & Notes
            $table->string('agreement_number')->nullable();
            $table->string('agreement_document')->nullable();
            $table->string('id_card_photo')->nullable();
            $table->text('notes')->nullable();

            // Status
            $table->string('status')->default('active'); // active, completed, terminated
            $table->date('actual_end_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_rentals');
    }
};

