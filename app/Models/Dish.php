<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    protected $table = 'dishes';
    protected $primaryKey = 'DishID';
    public $timestamps = false;

    protected $fillable = [
        'CategoryID', 'DishName', 'Description', 'Price', 'DishCode', 'Photo', 'Availability', 'Choices',
    ];

    protected $casts = [
        'Price' => 'decimal:2',
        'Availability' => 'boolean',
        'Choices' => 'array',
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

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->Photo ? asset('storage/'.$this->Photo) : null;
    }

    /**
     * Cleaned-up list of this dish's choice labels: blanks removed,
     * re-indexed, capped at 4. Empty array means "no choices" —
     * consumers should hide the Choice UI entirely in that case.
     */
    public function getChoiceListAttribute(): array
    {
        return collect($this->Choices ?? [])
            ->map(fn ($choice) => trim((string) $choice))
            ->filter(fn ($choice) => $choice !== '')
            ->values()
            ->take(4)
            ->all();
    }

    /**
     * Menu listing order: available dishes first, unavailable ones pushed last.
     */
    public function scopeMenuOrder($query)
    {
        return $query->orderByDesc('Availability')->orderBy('DishName');
    }
}