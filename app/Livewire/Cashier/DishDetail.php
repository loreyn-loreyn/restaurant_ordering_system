<?php

namespace App\Livewire\Cashier;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DishDetail extends Component
{
    public Dish $dish;

    public int $quantity = 1;

    public string $choice = 'Choice 1';

    public string $specialInstruction = '';

    public array $choices = ['Choice 1', 'Choice 2', 'Choice 3', 'Choice 4'];

    public function mount(Dish $dish)
    {
        if (! session('current_order_id')) {
            $this->redirectRoute('cashier.order-type', navigate: true);
            return;
        }

        $this->dish = $dish;
    }

    public function increment(): void
    {
        $this->quantity++;
    }

    public function decrement(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    /**
     * Adds the current selection to the cart (order_items) and
     * sends the cashier back to the menu they came from.
     */
    public function addToCart(): void
    {
        $this->saveItem();

        $this->redirectRoute('cashier.dishes', navigate: true);
    }

    /**
     * Adds the current selection to the cart, then jumps straight to the
     * Cart page with the Discounts & Comps modal opened, mirroring the
     * "buy just this one item" flow from the wireframes.
     */
    public function proceedToPayment(): void
    {
        $this->saveItem();

        session(['open_discount_modal' => true]);

        $this->redirectRoute('cashier.cart', navigate: true);
    }

    protected function saveItem(): void
    {
        $orderId = session('current_order_id');

        // If this dish (with the same choice) is already in the cart, bump the quantity
        // instead of creating a duplicate line.
        $instruction = $this->specialInstruction !== '' ? $this->specialInstruction : null;

        $existing = OrderItem::where('OrderID', $orderId)
            ->where('DishID', $this->dish->DishID)
            ->where('Choice', $this->choice)
            ->where('SpecialInstruction', $instruction)
            ->first();

        if ($existing) {
            $existing->update([
                'Quantity' => $existing->Quantity + $this->quantity,
            ]);
        } else {
            OrderItem::create([
                'OrderID' => $orderId,
                'DishID' => $this->dish->DishID,
                'Quantity' => $this->quantity,
                'ItemStatus' => 'R', // received into the cart
                'Choice' => $this->choice,
                'SpecialInstruction' => $this->specialInstruction !== '' ? $this->specialInstruction : null,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.cashier.dish-detail');
    }
}