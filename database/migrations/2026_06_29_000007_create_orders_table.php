<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('OrderID');
            $table->foreignId('UserID')
                ->constrained('users', 'UserID')
                ->onUpdate('cascade')->onDelete('cascade');
            // PaymentID FK constraint is added in a later migration,
            // once the payments table exists (orders <-> payments reference each other).
            $table->unsignedBigInteger('PaymentID')->nullable();
            $table->foreignId('DiscountID')
                ->nullable()
                ->constrained('discounts', 'DiscountID')
                ->onUpdate('cascade')->onDelete('set null');
            $table->boolean('OrderType');
            $table->boolean('OrderStatus')->default(false);
            $table->date('OrderDate');
            $table->decimal('TotalAmount', 10, 2);
            $table->decimal('Change', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};