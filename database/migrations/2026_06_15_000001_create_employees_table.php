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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // One-to-one link to the users table. unique() enforces that a user
            // can have at most one employee profile. cascadeOnDelete() removes the
            // employee row automatically if the user account is deleted.
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('employee_code')->unique();   // e.g. EMP-0001
            $table->string('phone', 20)->nullable();
            $table->string('designation');                // job title
            $table->string('department');
            $table->decimal('salary', 10, 2)->nullable(); // up to 99,999,999.99
            $table->date('hire_date');
            $table->string('status')->default('active');  // active | inactive

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
