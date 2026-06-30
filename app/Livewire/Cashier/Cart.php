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
    public Order $order;

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
        $orderId = session('current_order_id');

        if (! $orderId || ! Order::find($orderId)) {
            $this->redirectRoute('cashier.order-type', navigate: true);
            return;
        }

        $this->order = Order::with('items.dish', 'discount')->find($orderId);

        if ($this->order->DiscountID) {
            $this->discountId = $this->order->DiscountID;
            $this->discountType = $this->order->discount->Type;
        }

        // Came here via "Proceed to Payment" on the Dish Detail page.
        if (session()->pull('open_discount_modal')) {
            $this->showDiscountModal = true;
        }
    }

    protected function refreshOrder(): void
    {
        $this->order->refresh();
        $this->order->load('items.dish', 'discount');
    }

    public function increment(int $orderItemId): void
    {
        $item = OrderItem::where('OrderID', $this->order->OrderID)->findOrFail($orderItemId);
        $item->update(['Quantity' => $item->Quantity + 1]);
        $this->refreshOrder();
    }

    public function decrement(int $orderItemId): void
    {
        $item = OrderItem::where('OrderID', $this->order->OrderID)->findOrFail($orderItemId);

        if ($item->Quantity <= 1) {
            $item->delete();
        } else {
            $item->update(['Quantity' => $item->Quantity - 1]);
        }

        $this->refreshOrder();
    }

    public function removeItem(int $orderItemId): void
    {
        OrderItem::where('OrderID', $this->order->OrderID)->where('OrderItemID', $orderItemId)->delete();
        $this->refreshOrder();
    }

    public function goToMenu(): void
    {
        $this->redirectRoute('cashier.dishes', navigate: true);
    }

    // ---------------------------------------------------------------
    // Discounts & Comps
    // ---------------------------------------------------------------

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
        $this->order->update(['DiscountID' => $this->discountId]);
        $this->refreshOrder();
        $this->showDiscountModal = false;
        $this->showPaymentModal = true;
    }

    public function skipDiscount(): void
    {
        $this->order->update(['DiscountID' => null]);
        $this->discountId = null;
        $this->discountType = 'None';
        $this->refreshOrder();
        $this->showDiscountModal = false;
        $this->showPaymentModal = true;
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
        $this->showPaymentModal = false;
        $this->paymentSuccessful = false;
        $this->renderedAmount = '';
        $this->referenceNo = '';
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
        $toPay = $this->order->total_after_discount;

        return max($rendered - $toPay, 0);
    }

    /**
     * Finalizes the order: creates the payment row, links it to the order,
     * marks the order completed, and clears the in-progress cart session.
     */
    public function proceedPayment(): void
    {
        $this->validate([
            'paymentType' => 'required|in:Cash,Online/Card',
        ]);

        if ($this->paymentType === 'Cash') {
            $this->validate(['renderedAmount' => 'required|numeric|min:0']);
        } else {
            $this->validate(['referenceNo' => 'required']);
        }

        $toPay = $this->order->total_after_discount;
        $rendered = $this->paymentType === 'Cash' ? (float) $this->renderedAmount : $toPay;
        $change = max($rendered - $toPay, 0);

        $staffId = Auth::user()->staffDetails->StaffID ?? null;

        $payment = Payment::create([
            'OrderID' => $this->order->OrderID,
            'StaffID' => $staffId,
            'Method' => $this->paymentType === 'Cash' ? 'Cash' : 'Online/Card',
            'RenderedAmount' => $rendered,
            'Reference' => $this->paymentType === 'Cash' ? null : (int) $this->referenceNo,
            'TransactionDate' => now()->toDateString(),
        ]);

        $this->order->update([
            'PaymentID' => $payment->PaymentID,
            'OrderStatus' => true,
            'TotalAmount' => $toPay,
            'Change' => $change,
        ]);

        $this->paymentSuccessful = true;
    }

    public function finishTransaction(): void
    {
        session()->forget(['current_order_id', 'open_discount_modal']);
        $this->redirectRoute('cashier.order-type', navigate: true);
    }

    /**
     * Voids the whole in-progress order (cart) and starts over.
     */
    public function endTransaction(): void
    {
        OrderItem::where('OrderID', $this->order->OrderID)->delete();
        $this->order->delete();
        session()->forget(['current_order_id', 'open_discount_modal']);
        $this->redirectRoute('cashier.order-type', navigate: true);
    }

    public function render()
    {
        return view('livewire.cashier.cart');
    }
}
