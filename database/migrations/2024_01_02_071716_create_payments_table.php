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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_type_id')->constrained('payment_types')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreignId('commentable_id')->constrained('payment_types')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('paymentable_id');
            $table->string('paymentable_type');
            $table->integer('advance');
            $table->integer('Last3Digit');
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
