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
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('start_by')
            ->constrained('users')
            ->onDelete('cascade');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->foreignId('end_by')
            ->nullable()
            ->constrained('users')
            ->onDelete('cascade');
            $table->decimal('amount_input',10,2);
            $table->decimal('total_item_sale',10,0)->nullable();
            $table->decimal('total_amount',10,2)->nullable();
            $table->string('status')->default('Processing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_shift');
    }
};
