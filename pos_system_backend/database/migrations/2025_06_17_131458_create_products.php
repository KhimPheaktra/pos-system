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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
             $table->string('code')->unique()->nullable();
            $table->string('name')->nullable();
            $table->foreignId('category_id')->nullable()
            ->constrained('categories')
            ->onDelete('restrict');
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('discount', 5, 2)->nullable();
            $table->decimal('price_after_discount', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->decimal('current_qty',8,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
