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

    // Null when the dish has no choices configured; otherwise defaults
    // to the first configured choice.
    public ?string $choice = null;

    public string $specialInstruction = '';

    // Populated from the dish's own Manager-configured choices (0-4).
    // Empty means this dish has no choices, and the Choice section is
    // hidden entirely on the detail page.
    public array $choices = [];

    // Set when arriving here via "?cartItem={key}" from the Cart page —
    // means we're revisiting/customizing an existing cart line rather
    // than adding a brand new one.
    public ?string $editingCartKey = null;

    public function mount(Dish $dish)
    {
        if (! session('current_order_type')) {
            $this->redirectRoute('cashier.order-type', navigate: true);
            return;
        }

        $this->dish = $dish;
        $this->choices = $dish->ChoiceList;
        $this->choice = $this->choices[0] ?? null;

        $cartKey = request()->query('cartItem');

        if ($cartKey) {
            foreach (session('cart_items', []) as $item) {
                if ($item['key'] === $cartKey && $item['dish_id'] === $dish->DishID) {
                    $this->editingCartKey = $cartKey;
                    $this->quantity = $item['quantity'];
                    $this->specialInstruction = $item['special_instruction'] ?? '';

                    if (! empty($this->choices)) {
                        $this->choice = $item['choice'] ?? $this->choices[0];
                    }
                    break;
                }
            }
        }
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
        $this->redirectRoute($this->editingCartKey ? 'cashier.cart' : 'cashier.dishes', navigate: true);
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
        $choice = ! empty($this->choices) ? $this->choice : null;
        $items = session('cart_items', []);

        // Revisiting an existing line from the cart — update it in place
        // rather than merging into another line or creating a duplicate.
        if ($this->editingCartKey) {
            foreach ($items as &$item) {
                if ($item['key'] === $this->editingCartKey) {
                    $item['quantity'] = $this->quantity;
                    $item['choice'] = $choice;
                    $item['special_instruction'] = $instruction;
                    break;
                }
            }
            unset($item);

            session(['cart_items' => $items]);
            return;
        }

        // Find an existing line with the same dish + choice + instruction
        $found = false;
        foreach ($items as &$item) {
            if (
                $item['dish_id'] === $this->dish->DishID &&
                $item['choice'] === $choice &&
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
                'photo_url'          => $this->dish->PhotoUrl,
                'price'              => (float) $this->dish->Price,
                'quantity'           => $this->quantity,
                'choice'             => $choice,
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