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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_no');
            $table->string('reservation_id')->nullable();
            $table->timestamp('arrival_date');
            $table->timestamp('departure_date')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('check_in_made_by')->constrained('users');
            $table->foreignId('check_out_made_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
