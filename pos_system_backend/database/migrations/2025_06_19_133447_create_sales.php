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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->dateTime('sale_date');
            $table->foreignId('sale_by')->nullable() // if client buy in store show the user id that sale to client and if client buy from web or online dont show 
            ->constrained('users')
            ->onDelete('cascade');
 
            $table->timestamps();
          
        });

         Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->nullable()
            ->constrained('sales')
            ->onDelete('cascade');
            $table->foreignId('product_id')->nullable()
            ->constrained('products')
            ->onDelete('cascade');
            $table->decimal('qty',8,2);
            $table->decimal('price',10,2);
            $table->decimal('total_price_usd',10,2);
            $table->decimal('total_price_riel',10,2);
            $table->decimal('discount',5,2)->nullable();
            $table->foreignId('order_type_id')
            ->constrained('order_type')
            ->onDelete('cascade');
            $table->enum('status', ['complete', 'on_the_way', 'cancel'])->default('on_the_way');  //Complete, On the way , Cancel
            $table->decimal('amount_take_usd',10,2)->nullable(); //if client pay in usd bakorn bank or dollor 
            $table->decimal('amount_take_riel',10,2)->nullable(); //if client pay in riel bakorn bank or riel
            $table->decimal('amount_change_usd',10,2)->nullable(); //if client buy in store
            $table->decimal('amount_change_riel',10,2)->nullable(); //if client buy in store
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('sale_detials');
    }
};
