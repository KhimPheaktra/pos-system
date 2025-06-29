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
        Schema::create('user_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->foreignId('role_id')
            ->default('3')
            ->constrained('roles')
            ->onDelete('restrict');
            $table->string('image')->nullable();
            $table->string('status')->default('ACT');
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')
            ->nullable()
            ->constrained('users')
            ->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_client');
    }
};
