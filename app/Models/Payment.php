<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'PaymentID';
    public $timestamps = false;

    protected $fillable = [
        'OrderID', 'StaffID', 'Method', 'RenderedAmount', 'Reference', 'TransactionDate',
    ];

    protected $casts = [
        'RenderedAmount' => 'decimal:2',
        'TransactionDate' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'OrderID', 'OrderID');
    }

    public function staff()
    {
        return $this->belongsTo(StaffDetails::class, 'StaffID', 'StaffID');
    }
}