<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dishes', function (Blueprint $table) {
            $table->id('DishID');
            $table->foreignId('CategoryID')
                ->constrained('categories', 'CategoryID')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->string('DishName', 150);
            $table->string('Description', 255);
            $table->decimal('Price', 10, 2);
            $table->string('DishCode', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};
