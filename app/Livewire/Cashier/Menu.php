<?php

namespace App\Livewire\Cashier;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Menu extends Component
{
    public ?int $activeCategoryId = null;

    public function mount()
    {
        // Safety net: bounce back to order type selection if no cart/order in session.
        if (! session('current_order_id')) {
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
        $orderId = session('current_order_id');
        if (! $orderId) {
            return 0;
        }

        $order = Order::find($orderId);

        return $order ? $order->item_count : 0;
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