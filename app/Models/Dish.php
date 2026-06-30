<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    protected $table = 'dishes';
    protected $primaryKey = 'DishID';
    public $timestamps = false;

    protected $fillable = [
        'CategoryID', 'DishName', 'Description', 'Price', 'DishCode', 'Availability',
    ];

    protected $casts = [
        'Price' => 'decimal:2',
        'Availability' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'DishID';
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'CategoryID', 'CategoryID');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'DishID', 'DishID');
    }

    /**
     * Menu listing order: available dishes first, unavailable ones pushed last.
     */
    public function scopeMenuOrder($query)
    {
        return $query->orderByDesc('Availability')->orderBy('DishName');
    }
}