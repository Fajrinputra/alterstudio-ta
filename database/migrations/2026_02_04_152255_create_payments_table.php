<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel transaksi pembayaran untuk tiap booking (DP/FULL).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->enum('type', ['DP','FULL']);
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['PENDING','PAID','FAILED','EXPIRED'])->default('PENDING');
            $table->string('reference')->nullable();
            $table->string('order_id')->nullable()->unique();
            $table->string('snap_token')->nullable();
            $table->string('transaction_status')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
