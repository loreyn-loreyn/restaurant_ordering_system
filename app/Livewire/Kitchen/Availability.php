<?php

namespace App\Livewire\Kitchen;

use App\Models\Category;
use App\Models\Dish;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.kitchen')]
class Availability extends Component
{
    public ?int $activeCategoryId = null;

    public function selectCategory(?int $id): void
    {
        $this->activeCategoryId = $id;
    }

    /**
     * Toggle the dish's availability on/off.
     * Because the cashier's Menu component reads Dish::menuOrder() fresh on
     * every render, this change is immediately visible on the cashier side
     * the next time their page re-renders (or they navigate to it).
     */
    public function toggleAvailability(int $dishId): void
    {
        $dish = Dish::findOrFail($dishId);
        $dish->update(['Availability' => ! $dish->Availability]);
    }

    public function signOut(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        $categories = Category::all();

        $query = Dish::menuOrder();

        if ($this->activeCategoryId) {
            $query->where('CategoryID', $this->activeCategoryId);
        }

        return view('livewire.kitchen.availability', [
            'categories' => $categories,
            'dishes'     => $query->get(),
        ]);
    }
}
