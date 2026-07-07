<?php

namespace App\Livewire\Manager;

use App\Models\Dish;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.manager')]
class DishDetail extends Component
{
    public Dish $dish;

    public function mount(Dish $dish): void
    {
        $this->dish = $dish->load('category');
    }

    public function render()
    {
        return view('livewire.manager.dish-detail');
    }
}