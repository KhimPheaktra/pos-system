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
        Schema::create('product_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->nullable()
                ->constrained('products')
                ->onDelete('restrict');

            $table->string('old_name')->nullable();
            $table->string('new_name')->nullable();
            $table->decimal('old_price', 10, 2)->nullable();
            $table->decimal('new_price', 10, 2)->nullable();
            $table->decimal('old_qty', 8, 2)->nullable();
            $table->decimal('new_qty', 8, 2)->nullable();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')
                ->onDelete('restrict');
            $table->text('note')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_records');
    }
};
