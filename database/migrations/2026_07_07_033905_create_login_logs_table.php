<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id('LoginLogID');
            $table->foreignId('UserID')
                ->constrained('users', 'UserID')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('LoginAt');
            // Null while the session is (as far as we know) still active.
            // A row that stays null forever — with no matching session left
            // in the `sessions` table either — is exactly the "never
            // properly logged out" case (crash, closed tab, power loss, etc).
            $table->dateTime('LogoutAt')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};