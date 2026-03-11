<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pemesanan client terhadap paket layanan.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            // Client pemesan.
            $table->foreignId('client_id')->constrained('users');
            // Paket yang dipilih.
            $table->foreignId('package_id')->constrained('service_packages');
            $table->dateTime('booking_date');
            $table->string('location');
            $table->text('notes')->nullable();
            $table->enum('status', ['DRAFT','WAITING_PAYMENT','DP_PAID','PAID','CANCELLED'])->default('WAITING_PAYMENT');
            $table->unsignedBigInteger('total_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
