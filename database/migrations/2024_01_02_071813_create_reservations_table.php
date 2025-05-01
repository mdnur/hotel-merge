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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->integer('reservation_no');
            $table->date('booking_date');
            $table->foreignId('customer_id')->constrained('customers');
            // $table->foreignId('payment_id')->constrained('payments');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('total_rent');
            $table->timestamp('confrim_message_sent_at')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ['confirm', 'arrived', 'cancelled'])->default('confirm');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
