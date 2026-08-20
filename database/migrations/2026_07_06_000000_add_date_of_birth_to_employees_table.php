<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the employee's date of birth (used for birthday wishes on the dashboard).
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('hire_date');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('date_of_birth');
        });
    }
};
