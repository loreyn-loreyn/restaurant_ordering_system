<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances';
    protected $primaryKey = 'AttendanceID';
    public $timestamps = false;

    protected $fillable = [
        'StaffID', 'AttendanceDate', 'Status', 'TimeIn', 'TimeOut', 'Note',
    ];

    protected $casts = [
        'AttendanceDate' => 'datetime',
    ];

    // Status: 'P' Present, 'A' Absent, 'L' Leave
    public function staff()
    {
        return $this->belongsTo(StaffDetails::class, 'StaffID', 'StaffID');
    }

    public function getHoursWorkedAttribute(): float
    {
        if (! $this->TimeIn || ! $this->TimeOut) {
            return 0.0;
        }

        return round(
            (strtotime($this->TimeOut) - strtotime($this->TimeIn)) / 3600,
            2
        );
    }
}
