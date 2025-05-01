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
        Schema::create('card_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained();
            $table->foreignId('room_id')->constrained();
            $table->timestamp('check_in');
            $table->timestamp('check_out');
            $table->decimal('rent', 8, 2);
            $table->string('note')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_rooms');
    }
};
