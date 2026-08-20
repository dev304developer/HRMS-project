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
        Schema::table('attendances', function (Blueprint $table) {
            // Break duration in minutes for the session. Subtracted from gross
            // worked time to give productive hours. Defaults to 0 (no break).
            $table->unsignedInteger('break_minutes')->default(0)->after('clock_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('break_minutes');
        });
    }
};
