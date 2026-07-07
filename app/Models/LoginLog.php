<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $table = 'login_logs';
    protected $primaryKey = 'LoginLogID';
    public $timestamps = false;

    protected $fillable = ['UserID', 'LoginAt', 'LogoutAt'];

    protected $casts = [
        'LoginAt' => 'datetime',
        'LogoutAt' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
}