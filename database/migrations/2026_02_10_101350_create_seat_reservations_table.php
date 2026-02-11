<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seat_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_trip_id')->constrained('bus_trips')->onDelete('cascade');
            $table->foreignId('seat_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['reserved', 'confirmed'])->default('reserved');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['bus_trip_id', 'seat_id', 'status'], 'unique_seat_trip'); // Simplified
            $table->unique(['bus_trip_id', 'user_id']); // One reservation per user per trip
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seat_reservations');
    }
};
