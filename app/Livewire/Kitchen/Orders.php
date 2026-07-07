<?php

namespace App\Livewire\Kitchen;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.kitchen')]
class Orders extends Component
{
    public ?int $activeOrderId    = null;
    public ?int $activeCategoryId = null;

    public function selectCategory(?int $id): void
    {
        $this->activeCategoryId = $id;
    }

    public function openOrder(int $orderId): void
    {
        $this->activeOrderId = $orderId;
    }

    public function closeOrder(): void
    {
        $this->activeOrderId = null;
    }

    /**
     * Advance a single item through the kitchen workflow:
     *   S (start) → P (preparing) → R (ready)
     */
    public function advanceStatus(int $orderItemId): void
    {
        $item = OrderItem::findOrFail($orderItemId);

        $next = match ($item->ItemStatus) {
            'S'     => 'P',
            'P'     => 'R',
            default => $item->ItemStatus, // R stays R
        };

        $item->update(['ItemStatus' => $next]);
    }

    /**
     * Kitchen confirms the order is fully prepared.
     * Guard: all items must already be S before this runs.
     * After closing the modal, render() re-fetches and the order
     * naturally falls out of the pending list.
     */
    public function completeOrder(int $orderId): void
    {
        $order = Order::with('items')->find($orderId);

        if (! $order) {
            $this->activeOrderId = null;
            return;
        }

        if (! $order->items->every(fn ($i) => $i->ItemStatus === 'R')) {
            return; // safety guard — button should already be disabled
        }

        $order->update(['OrderStatus' => false]);
        $order->items()->update(['ItemStatus' => 'S']);

        $this->activeOrderId = null;
    }

    public function signOut(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        $categories = Category::all();

        // Pending kitchen orders: paid by cashier, but not all items served yet.
        $query = Order::with('items.dish')
            ->where('OrderStatus', true)
            ->orderBy('OrderID');

        // Optional category filter: show only orders that contain items from that category.
        if ($this->activeCategoryId) {
            $query->whereHas('items.dish', fn ($q) =>
                $q->where('CategoryID', $this->activeCategoryId)
            );
        }

        $allOrders   = $query->get();
        $totalOrders = $allOrders->count();

        // First 5 shown in full; if there are more, the 6th slot is a dimmed
        // overflow card showing the 6th order's data + a "+N" counter.
        $overflow  = max(0, $totalOrders - 5);
        $displayed = $allOrders->take(min($totalOrders, 6));

        // If the popup's order disappeared (all items became S via poll), close it.
        if ($this->activeOrderId && ! $allOrders->contains('OrderID', $this->activeOrderId)) {
            $this->activeOrderId = null;
        }

        // Always fetch the popup order fresh so status changes reflect immediately.
        $activeOrder = $this->activeOrderId
            ? Order::with('items.dish')->find($this->activeOrderId)
            : null;

        return view('livewire.kitchen.orders', compact(
            'categories', 'displayed', 'overflow', 'activeOrder'
        ));
    }
}