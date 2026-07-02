<?php

namespace App\Livewire\Cashier;

use App\Models\Dish;
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
        if (! session('current_order_type')) {
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

    public function addToCart(): void
    {
        $this->saveItem();
        $this->redirectRoute('cashier.dishes', navigate: true);
    }

    public function proceedToPayment(): void
    {
        $this->saveItem();
        session(['open_discount_modal' => true]);
        $this->redirectRoute('cashier.cart', navigate: true);
    }

    /**
     * Saves the item into the session cart only — no DB writes here.
     * The cart is a list of arrays stored under 'cart_items' in session.
     */
    protected function saveItem(): void
    {
        $instruction = $this->specialInstruction !== '' ? $this->specialInstruction : null;
        $items = session('cart_items', []);

        // Find an existing line with the same dish + choice + instruction
        $found = false;
        foreach ($items as &$item) {
            if (
                $item['dish_id'] === $this->dish->DishID &&
                $item['choice'] === $this->choice &&
                $item['special_instruction'] === $instruction
            ) {
                $item['quantity'] += $this->quantity;
                $found = true;
                break;
            }
        }
        unset($item);

        if (! $found) {
            $items[] = [
                'key'                => uniqid(),
                'dish_id'            => $this->dish->DishID,
                'dish_name'          => $this->dish->DishName,
                'price'              => (float) $this->dish->Price,
                'quantity'           => $this->quantity,
                'choice'             => $this->choice,
                'special_instruction'=> $instruction,
            ];
        }

        session(['cart_items' => $items]);
    }

    public function render()
    {
        return view('livewire.cashier.dish-detail');
    }
}