<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $table = 'discounts';
    protected $primaryKey = 'DiscountID';
    public $timestamps = false;

    protected $fillable = ['Type', 'Reason', 'Amount'];

    protected $casts = [
        'Amount' => 'decimal:2',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'DiscountID', 'DiscountID');
    }
}