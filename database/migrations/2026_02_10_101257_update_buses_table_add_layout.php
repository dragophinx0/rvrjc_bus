<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->enum('layout_type', ['2x2', '3x2'])->default('2x2')->after('capacity');
            $table->integer('rows')->default(10)->after('layout_type');
            $table->foreignId('current_trip_id')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->dropColumn(['layout_type', 'rows', 'current_trip_id']);
        });
    }
};
