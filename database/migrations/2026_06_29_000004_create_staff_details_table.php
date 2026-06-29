<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_details', function (Blueprint $table) {
            $table->string('StaffID', 20)->primary();
            $table->foreignId('UserID')
                ->constrained('users', 'UserID')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->string('LastName', 100);
            $table->string('FirstName', 100);
            $table->string('MiddleName', 100);
            $table->integer('Age');
            $table->date('BirthDate');
            $table->char('Sex', 1);
            $table->string('BirthPlace', 150);
            $table->string('Nationality', 100);
            $table->string('Address', 255);
            $table->string('ContactNumber', 20);
            $table->string('Email', 150);
            $table->date('HiredDate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_details');
    }
};
