<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('UserID');
            $table->foreignId('RoleID')
                ->constrained('roles', 'RoleID')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->string('UserName', 100);
            $table->string('Password', 255);
            $table->date('DateIssued');
            $table->boolean('AccountStatus')->default(false);
            $table->boolean('AccountApprovalStatus')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
