<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffDetails extends Model
{
    protected $table = 'staff_details';
    protected $primaryKey = 'StaffID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'StaffID', 'UserID', 'RoleID', 'LastName', 'FirstName', 'MiddleName', 'Photo',
        'Age', 'BirthDate', 'Sex', 'BirthPlace', 'Nationality',
        'Address', 'ContactNumber', 'Email', 'HiredDate',
    ];

    protected $casts = [
        'Age' => 'integer',
        'BirthDate' => 'date',
        'HiredDate' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    // The role assigned at intake by the Manager — separate from the
    // account's role, which Admin sets (and may differ) when creating the login.
    public function role()
    {
        return $this->belongsTo(Role::class, 'RoleID', 'RoleID');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'StaffID', 'StaffID');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'StaffID', 'StaffID');
    }

    public function getFullNameAttribute(): string
    {
        $middleInitial = $this->MiddleName ? strtoupper(substr($this->MiddleName, 0, 1)).'.' : '';

        return trim("{$this->LastName}, {$this->FirstName} {$middleInitial}");
    }

    /**
     * "Section" prefers the assigned RoleID directly on this record
     * (set by the Manager at intake). Falls back to the linked account's
     * role for legacy rows that only ever had users.RoleID.
     */
    public function getSectionAttribute(): ?string
    {
        return $this->role?->RoleName ?? $this->user?->role?->RoleName;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->Photo ? asset('storage/'.$this->Photo) : null;
    }

    public function getHasAccountAttribute(): bool
    {
        return ! is_null($this->UserID);
    }
}