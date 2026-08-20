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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();

            // Each leave request belongs to one employee. Deleting the employee
            // removes their leave records too.
            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('leave_type');                  // annual | sick | casual | unpaid
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');  // pending | approved | rejected
            $table->index('status');                       // dashboard filters by status

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
