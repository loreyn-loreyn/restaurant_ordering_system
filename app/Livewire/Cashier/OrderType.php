<?php

namespace App\Livewire\Cashier;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class OrderType extends Component
{
    /**
     * Handle the cashier picking Dine-in or Take-out for a new order.
     * Creates the pending Order row (acts as the "cart" header) and
     * stores its OrderID in session so every later screen knows which
     * order is being built.
     */
    public function selectType(string $type): void
    {
        // If there's an existing uncommitted (unpaid) order in session, delete it cleanly.
        $existingId = session('current_order_id');
        if ($existingId) {
            $existing = Order::find($existingId);
            if ($existing && ! $existing->OrderStatus) {
                OrderItem::where('OrderID', $existingId)->delete();
                $existing->delete();
            }
        }

        // Just store the chosen type — the Order row is created only when the first item is added.
        session([
            'current_order_id'   => null,
            'current_order_type' => $type,
        ]);

        $this->redirectRoute('cashier.dishes', navigate: true);
    }

    public function signOut(): void
    {
        session()->forget(['cart_items', 'current_order_type', 'current_order_id']);
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        return view('livewire.cashier.order-type');
    }
}