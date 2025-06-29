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
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->foreignId('gender_id')
            ->constrained('genders')
            ->onDelete('restrict');
            $table->date('dob')->nullable();
            $table->foreignId('pob')->nullable()
            ->constrained('provinces')
            ->onDelete('restrict');
            $table->decimal('salary',10,2)->nullable();
            $table->string('image')->nullable();
            $table->string('status')->default('ACT');
            $table->foreignId('position_id')
            ->constrained('positions')
            ->onDelete('restrict');
             $table->foreignId('created_by')
            ->nullable()
            ->constrained('users')
            ->onDelete('set null');
            $table->foreignId('updated_by')
            ->nullable()
            ->constrained('users')
            ->onDelete('set null');
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
