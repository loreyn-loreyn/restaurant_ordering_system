<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id('AttendanceID');
            $table->string('StaffID', 20);
            $table->foreign('StaffID')
                ->references('StaffID')->on('staff_details')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->timestamp('AttendanceDate')->useCurrent();
            $table->char('Status', 1);
            $table->time('TimeIn');
            $table->time('TimeOut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
