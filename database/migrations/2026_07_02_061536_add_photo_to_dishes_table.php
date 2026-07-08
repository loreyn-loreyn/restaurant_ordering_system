<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            // Stored path relative to the "public" disk, e.g. "dish-photos/xxxx.jpg"
            $table->string('Photo', 255)->nullable()->after('DishCode');
        });
    }

    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn('Photo');
        });
    }
};
