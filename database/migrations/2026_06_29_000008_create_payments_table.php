<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('PaymentID');
            $table->foreignId('OrderID')
                ->constrained('orders', 'OrderID')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->string('StaffID', 20);
            $table->foreign('StaffID')
                ->references('StaffID')->on('staff_details')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->string('Method', 50);
            $table->decimal('RenderedAmount', 10, 2);
            $table->integer('Reference')->nullable();
            $table->date('TransactionDate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
