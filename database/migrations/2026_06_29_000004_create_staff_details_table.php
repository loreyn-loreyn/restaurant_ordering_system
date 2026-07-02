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
            // Nullable: a manager-submitted staff record has no linked account
            // until an Admin reviews it and creates the login (users row).
            $table->foreignId('UserID')->nullable()
                ->constrained('users', 'UserID')
                ->onUpdate('cascade')->onDelete('cascade');
            // The role the Manager assigns at intake — independent of the
            // account's RoleID, which Admin sets when the login is created.
            // Also drives the StaffID prefix (e.g. Cashier -> C001).
            $table->foreignId('RoleID')->nullable()
                ->constrained('roles', 'RoleID')
                ->onUpdate('cascade')->onDelete('set null');
            $table->string('LastName', 100);
            $table->string('FirstName', 100);
            $table->string('MiddleName', 100)->nullable();
            $table->string('Photo', 255)->nullable();
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