<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'UserID';

    protected $fillable = [
        'RoleID',
        'UserName',
        'Password',
        'DateIssued',
        'AccountStatus',
        'AccountApprovalStatus',
    ];

    protected $hidden = [
        'Password',
    ];

    protected $casts = [
        'DateIssued' => 'date',
        'AccountStatus' => 'boolean',
        'AccountApprovalStatus' => 'boolean',
    ];

    // Tell Laravel which column holds the hashed password
    public function getAuthPassword()
    {
        return $this->Password;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'RoleID', 'RoleID');
    }

    public function staffDetails()
    {
        return $this->hasOne(StaffDetails::class, 'UserID', 'UserID');
    }
}
