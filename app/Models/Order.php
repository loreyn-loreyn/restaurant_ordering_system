<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'OrderID';
    public $timestamps = false;

    protected $fillable = [
        'UserID', 'PaymentID', 'DiscountID', 'OrderType', 'OrderStatus',
        'OrderDate', 'TotalAmount', 'Change',
    ];

    protected $casts = [
        'OrderType' => 'boolean',   // true = Dine-in, false = Take-out
        'OrderStatus' => 'boolean', // false = pending (cart), true = completed/paid
        'OrderDate' => 'date',
        'TotalAmount' => 'decimal:2',
        'Change' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'PaymentID', 'PaymentID');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'DiscountID', 'DiscountID');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'OrderID', 'OrderID');
    }

    public function getOrderTypeLabelAttribute(): string
    {
        return $this->OrderType ? 'Dine-in' : 'Take-out';
    }

    /**
     * Sum of (dish price * quantity) for every item currently in the cart,
     * before any discount/comp is applied.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn ($item) => $item->dish->Price * $item->Quantity);
    }

    /**
     * Subtotal minus the flat amount of the attached discount/comp, floored at 0.
     */
    public function getTotalAfterDiscountAttribute(): float
    {
        $subtotal = $this->subtotal;
        $discountAmount = $this->discount->Amount ?? 0;

        return max($subtotal - $discountAmount, 0);
    }

    public function getItemCountAttribute(): int
    {
        return (int) $this->items->sum('Quantity');
    }
}