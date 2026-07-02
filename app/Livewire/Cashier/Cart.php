<?php

namespace App\Livewire\Cashier;

use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Cart extends Component
{
    // Discounts & Comps modal state
    public bool $showDiscountModal = false;
    public string $discountType = 'None';
    public ?int $discountId = null;

    // Payment modal state
    public bool $showPaymentModal = false;
    public string $paymentType = 'Cash';
    public string $renderedAmount = '';
    public string $referenceNo = '';
    public bool $paymentSuccessful = false;

    public function mount()
    {
        if (! session('current_order_type')) {
            $this->redirectRoute('cashier.order-type', navigate: true);
            return;
        }

        if (session()->pull('open_discount_modal')) {
            $this->showDiscountModal = true;
        }
    }

    // ---------------------------------------------------------------
    // Session cart helpers
    // ---------------------------------------------------------------

    protected function getItems(): array
    {
        return session('cart_items', []);
    }

    protected function saveItems(array $items): void
    {
        session(['cart_items' => array_values($items)]);
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->getItems())->sum(fn ($i) => $i['price'] * $i['quantity']);
    }

    public function getTotalAfterDiscountProperty(): float
    {
        $subtotal = $this->subtotal;

        if (! $this->discountId || $this->discountId === 0) {
            return $subtotal;
        }

        $discount = Discount::find((int) $this->discountId);
        if (! $discount) {
            return $subtotal;
        }

        return max($subtotal - (float) $discount->Amount, 0);
    }

    public function increment(string $key): void
    {
        $items = $this->getItems();
        foreach ($items as &$item) {
            if ($item['key'] === $key) {
                $item['quantity']++;
                break;
            }
        }
        unset($item);
        $this->saveItems($items);
    }

    public function decrement(string $key): void
    {
        $items = $this->getItems();
        foreach ($items as $index => $item) {
            if ($item['key'] === $key) {
                if ($item['quantity'] <= 1) {
                    unset($items[$index]);
                } else {
                    $items[$index]['quantity']--;
                }
                break;
            }
        }
        $this->saveItems($items);
    }

    public function removeItem(string $key): void
    {
        $items = $this->getItems();
        $items = array_filter($items, fn ($i) => $i['key'] !== $key);
        $this->saveItems($items);
    }

    public function goToMenu(): void
    {
        $this->redirectRoute('cashier.dishes', navigate: true);
    }

    // ---------------------------------------------------------------
    // Discounts & Comps
    // ---------------------------------------------------------------

    public function updatedDiscountType(): void
    {
        $this->discountId = null;
    }

    public function openDiscountModal(): void
    {
        $this->showDiscountModal = true;
    }

    public function closeDiscountModal(): void
    {
        $this->showDiscountModal = false;
    }

    public function getDiscountOptionsProperty()
    {
        if ($this->discountType === 'None') {
            return collect();
        }

        return Discount::where('Type', $this->discountType)->get();
    }

    public function applyDiscount(): void
    {
        $this->showDiscountModal = false;
        $this->showPaymentModal  = true;
    }

    public function skipDiscount(): void
    {
        $this->discountId        = null;
        $this->discountType      = 'None';
        $this->showDiscountModal = false;
        $this->showPaymentModal  = true;
    }

    /**
     * From the Payment modal, go back to the Discount modal.
     * Previously picked type/reason are preserved in component state.
     */
    public function backToDiscount(): void
    {
        $this->showPaymentModal  = false;
        $this->showDiscountModal = true;
    }

    /**
     * Live preview of the discount amount for the currently selected reason,
     * without needing to persist to the DB first.
     */
    public function getPreviewDiscountAmountProperty(): float
    {
        if (! $this->discountId || $this->discountId === 0) {
            return 0.0;
        }

        $discount = Discount::find((int) $this->discountId);

        return $discount ? (float) $discount->Amount : 0.0;
    }

    // ---------------------------------------------------------------
    // Payment
    // ---------------------------------------------------------------

    public function openPaymentModal(): void
    {
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal  = false;
        $this->paymentSuccessful = false;
        $this->renderedAmount    = '';
        $this->referenceNo       = '';
    }

    public function pressKey(string $key): void
    {
        $field = $this->paymentType === 'Cash' ? 'renderedAmount' : 'referenceNo';

        if ($key === '.' && str_contains($this->{$field}, '.')) {
            return;
        }

        $this->{$field} .= $key;
    }

    public function clearKeypad(): void
    {
        if ($this->paymentType === 'Cash') {
            $this->renderedAmount = '';
        } else {
            $this->referenceNo = '';
        }
    }

    public function getChangeProperty(): float
    {
        $rendered = (float) ($this->renderedAmount ?: 0);

        return max($rendered - $this->totalAfterDiscount, 0);
    }

    /**
     * Only here — on confirmed payment — do we write to the DB.
     * Creates Order + OrderItems + Payment in one go.
     */
    public function proceedPayment(): void
    {
        $this->validate([
            'paymentType' => 'required|in:Cash,Online/Card',
        ]);

        $toPay = $this->totalAfterDiscount;

        if ($this->paymentType === 'Cash') {
            $this->validate(['renderedAmount' => 'required|numeric|min:0']);

            if ((float) $this->renderedAmount < $toPay) {
                $this->addError('renderedAmount', 'Amount not enough, must be at least $' . number_format($toPay, 2) . '.');
                return;
            }
        } else {
            $this->validate(['referenceNo' => 'required']);
        }

        $items    = $this->getItems();
        $type     = session('current_order_type');
        $rendered = $this->paymentType === 'Cash' ? (float) $this->renderedAmount : $toPay;
        $change   = max($rendered - $toPay, 0);
        $staffId  = Auth::user()->staffDetails->StaffID ?? null;

        // 1. Create the Order row
        $order = Order::create([
            'UserID'      => Auth::id(),
            'PaymentID'   => null,
            'DiscountID'  => $this->discountId,
            'OrderType'   => $type === 'Dine-in',
            'OrderStatus' => true,
            'OrderDate'   => now()->toDateString(),
            'TotalAmount' => $toPay,
            'Change'      => $change,
        ]);

        // 2. Create OrderItems
        foreach ($items as $item) {
            OrderItem::create([
                'OrderID'            => $order->OrderID,
                'DishID'             => $item['dish_id'],
                'Quantity'           => $item['quantity'],
                'ItemStatus'         => 'S',
                'Choice'             => $item['choice'],
                'SpecialInstruction' => $item['special_instruction'],
            ]);
        }

        // 3. Create Payment and link back to Order
        $payment = Payment::create([
            'OrderID'         => $order->OrderID,
            'StaffID'         => $staffId,
            'Method'          => $this->paymentType === 'Cash' ? 'Cash' : 'Online/Card',
            'RenderedAmount'  => $rendered,
            'Reference'       => $this->paymentType === 'Cash' ? null : (int) $this->referenceNo,
            'TransactionDate' => now()->toDateString(),
        ]);

        $order->update(['PaymentID' => $payment->PaymentID]);

        $this->paymentSuccessful = true;
    }

    public function finishTransaction(): void
    {
        session()->forget(['cart_items', 'current_order_type', 'current_order_id', 'open_discount_modal']);
        $this->redirectRoute('cashier.order-type', navigate: true);
    }

    /**
     * Voids the whole in-progress cart and starts over.
     * Nothing to delete from DB since items only exist in session until payment.
     */
    public function endTransaction(): void
    {
        session()->forget(['cart_items', 'current_order_type', 'current_order_id', 'open_discount_modal']);
        $this->redirectRoute('cashier.order-type', navigate: true);
    }

    public function render()
    {
        return view('livewire.cashier.cart', [
            'items' => collect($this->getItems()),
        ]);
    }
}