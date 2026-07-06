<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'CategoryID';
    public $timestamps = false;

    protected $fillable = ['CategoryName'];

    public function dishes()
    {
        return $this->hasMany(Dish::class, 'CategoryID', 'CategoryID');
    }
}