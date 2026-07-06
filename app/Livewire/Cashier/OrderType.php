<?php

namespace App\Livewire\Cashier;

use App\Models\Order;
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
        $order = Order::create([
            'UserID' => Auth::id(),
            'PaymentID' => null,
            'DiscountID' => null,
            'OrderType' => $type === 'Dine-in', // true = Dine-in, false = Take-out
            'OrderStatus' => false,             // pending / still in cart
            'OrderDate' => now()->toDateString(),
            'TotalAmount' => 0,
            'Change' => 0,
        ]);

        session(['current_order_id' => $order->OrderID]);

        $this->dispatch('order-type-selected', type: $type);

        $this->redirectRoute('cashier.dishes', navigate: true);
    }

    public function render()
    {
        return view('livewire.cashier.order-type');
    }
}