<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id('DiscountID');
            $table->string('Type', 50);
            $table->string('Reason', 150);
            $table->decimal('Amount', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};