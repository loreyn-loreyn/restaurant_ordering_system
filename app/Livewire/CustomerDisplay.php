<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Layout;


#[Layout('layouts.app')]
class CustomerDisplay extends Component
{
    public function render()
    {
        // Pending: paid but kitchen hasn't completed them yet
        $serving = Order::with('items.dish')
            ->where('OrderStatus', true)
            ->orderBy('OrderID')
            ->get();

        // Completed: kitchen marked done (OrderStatus = false) and has a payment
        $completed = Order::whereNotNull('PaymentID')
            ->where('OrderStatus', false)
            ->orderByDesc('OrderID')
            ->take(10)
            ->get();

        return view('livewire.customer-display', compact('serving', 'completed'));
    }
}