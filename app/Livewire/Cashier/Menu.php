<?php

namespace App\Livewire\Cashier;

use App\Models\Category;
use App\Models\Dish;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Menu extends Component
{
    public ?int $activeCategoryId = null;

    public function mount()
    {
        if (! session('current_order_type')) {
            $this->redirectRoute('cashier.order-type', navigate: true);
        }
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->activeCategoryId = $categoryId;
    }

    public function goToCart(): void
    {
        $this->redirectRoute('cashier.cart', navigate: true);
    }

    public function changeOrderType(): void
    {
        session()->forget(['cart_items', 'current_order_type', 'current_order_id']);
        $this->redirectRoute('cashier.order-type', navigate: true);
    }

    public function signOut(): void
    {
        session()->forget('current_order_id');
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->redirectRoute('login', navigate: true);
    }

    public function getCartCountProperty(): int
    {
        return collect(session('cart_items', []))->sum('quantity');
    }

    public function render()
    {
        $categories = Category::all();

        $dishesQuery = Dish::menuOrder();

        if ($this->activeCategoryId) {
            $dishesQuery->where('CategoryID', $this->activeCategoryId);
        }

        return view('livewire.cashier.menu', [
            'categories' => $categories,
            'dishes' => $dishesQuery->get(),
            'cartCount' => $this->cartCount,
        ]);
    }
}