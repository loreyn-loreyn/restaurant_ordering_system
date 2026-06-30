<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('OrderItemID');
            $table->foreignId('OrderID')
                ->constrained('orders', 'OrderID')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('DishID')
                ->constrained('dishes', 'DishID')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->integer('Quantity');
            $table->char('ItemStatus', 1);
            $table->string('Choice', 150);
            $table->string('SpecialInstruction', 255) -> nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
