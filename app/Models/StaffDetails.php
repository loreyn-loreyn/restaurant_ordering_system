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
        'StaffID', 'UserID', 'LastName', 'FirstName', 'MiddleName',
        'Age', 'BirthDate', 'Sex', 'BirthPlace', 'Nationality',
        'Address', 'ContactNumber', 'Email', 'HiredDate',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
}
