<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $primaryKey = 'OrderItemID';
    public $timestamps = false;

    protected $fillable = [
        'OrderID', 'DishID', 'Quantity', 'ItemStatus', 'Choice', 'SpecialInstruction',
    ];

    protected $casts = [
        'Quantity' => 'integer',
    ];

    // ItemStatus values used while moving through the kitchen workflow:
    // R = received/cart item, P = preparing, S = served/ready
    public function order()
    {
        return $this->belongsTo(Order::class, 'OrderID', 'OrderID');
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class, 'DishID', 'DishID');
    }

    public function getLineTotalAttribute(): float
    {
        return (float) ($this->dish->Price * $this->Quantity);
    }
}