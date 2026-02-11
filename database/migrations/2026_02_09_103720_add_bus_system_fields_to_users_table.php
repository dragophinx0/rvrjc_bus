<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role and verification
            $table->enum('role', ['admin', 'bus_coordinator', 'driver', 'faculty', 'student'])->after('email');
            $table->string('first_name')->after('name');
            $table->string('last_name')->after('first_name');
            $table->string('mobile_number', 15)->after('last_name');
            $table->boolean('is_verified')->default(false)->after('password');
            $table->foreignId('verified_by')->nullable()->after('is_verified')->constrained('users')->cascadeOnDelete()->nullOnDelete();

            // Student fields
            $table->string('roll_number')->unique()->nullable()->after('verified_by');
            $table->enum('course', ['B.Tech', 'M.Tech', 'MBA', 'BBA', 'MCA'])->nullable()->after('roll_number');
            $table->foreignId('branch_id')->nullable()->after('course')->constrained('branches')->nullOnDelete();
            $table->enum('year', ['1', '2', '3', '4'])->nullable()->after('branch_id');
            $table->date('date_of_birth')->nullable()->after('year');

            // Faculty fields
            $table->string('employee_id')->unique()->nullable()->after('date_of_birth');
            $table->foreignId('designation_id')->nullable()->after('employee_id')->constrained('designations')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('designation_id')->constrained('branches')->nullOnDelete();

            // Driver fields (currently selected bus)
            $table->foreignId('current_bus_id')->nullable()->after('department_id')->constrained('buses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['designation_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['current_bus_id']);

            $table->dropColumn([
                'role',
                'first_name',
                'last_name',
                'mobile_number',
                'is_verified',
                'verified_by',
                'roll_number',
                'course',
                'branch_id',
                'year',
                'date_of_birth',
                'employee_id',
                'designation_id',
                'department_id',
                'current_bus_id'
            ]);
        });
    }
};
