<?php

namespace Tests\Feature;

use App\Livewire\Kitchen\Availability;
use App\Livewire\Kitchen\Orders;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KitchenOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('UserName', 'K003')->firstOrFail());
    }

    protected function makeOrder(bool $orderStatus = true): Order
    {
        return Order::create([
            'UserID' => 3,
            'PaymentID' => null,
            'DiscountID' => null,
            'OrderType' => true,
            'OrderStatus' => $orderStatus,
            'OrderDate' => now()->toDateString(),
            'TotalAmount' => 100,
            'Change' => 0,
        ]);
    }

    // ── Listing ──────────────────────────────────────────────────────

    public function test_only_orders_with_orderstatus_true_are_shown_as_pending(): void
    {
        // Seeded OrderID 1 & 2 have OrderStatus true, OrderID 3 has false.
        $displayed = Livewire::test(Orders::class)->viewData('displayed');

        $this->assertTrue($displayed->contains('OrderID', 1));
        $this->assertTrue($displayed->contains('OrderID', 2));
        $this->assertFalse($displayed->contains('OrderID', 3));
    }

    public function test_category_filter_only_shows_orders_containing_that_category(): void
    {
        // OrderID 2 seeded with DishID 2 (Main Dish, CategoryID 1) and
        // DishID 5 (Drinks, CategoryID 3). OrderID 1 has DishID 1 (Main
        // Dish) and DishID 4 (Drinks). Filtering to Dessert (CategoryID 2)
        // should exclude both, since neither order contains a dessert.
        $displayed = Livewire::test(Orders::class)
            ->call('selectCategory', 2)
            ->viewData('displayed');

        $this->assertCount(0, $displayed);
    }

    public function test_overflow_counter_when_more_than_six_pending_orders(): void
    {
        // 2 seeded pending orders + 6 new ones = 8 total; only 6 display,
        // the rest reported via $overflow.
        for ($i = 0; $i < 6; $i++) {
            $this->makeOrder(true);
        }

        $component = Livewire::test(Orders::class);

        $this->assertCount(6, $component->viewData('displayed'));
        $this->assertSame(2, $component->viewData('overflow'));
    }

    // ── Status progression ──────────────────────────────────────────

    public function test_advance_status_moves_s_to_p_to_r(): void
    {
        $item = OrderItem::findOrFail(3); // seeded ItemStatus 'S'

        Livewire::test(Orders::class)->call('advanceStatus', $item->OrderItemID);
        $this->assertSame('P', $item->fresh()->ItemStatus);

        Livewire::test(Orders::class)->call('advanceStatus', $item->OrderItemID);
        $this->assertSame('R', $item->fresh()->ItemStatus);
    }

    public function test_advance_status_is_a_no_op_once_ready(): void
    {
        $item = OrderItem::findOrFail(3);
        $item->update(['ItemStatus' => 'R']);

        Livewire::test(Orders::class)->call('advanceStatus', $item->OrderItemID);

        $this->assertSame('R', $item->fresh()->ItemStatus);
    }

    // ── completeOrder guard ──────────────────────────────────────────

    public function test_complete_order_is_blocked_until_every_item_is_ready(): void
    {
        $order = $this->makeOrder(true);
        $dish = Dish::findOrFail(1);
        OrderItem::create(['OrderID' => $order->OrderID, 'DishID' => $dish->DishID, 'Quantity' => 1, 'ItemStatus' => 'S', 'Choice' => 'Regular']);
        OrderItem::create(['OrderID' => $order->OrderID, 'DishID' => $dish->DishID, 'Quantity' => 1, 'ItemStatus' => 'R', 'Choice' => 'Regular']);

        Livewire::test(Orders::class)->call('completeOrder', $order->OrderID);

        $this->assertTrue((bool) $order->fresh()->OrderStatus, 'Order should remain pending while any item is not R.');
    }

    public function test_complete_order_closes_the_order_and_resets_items_to_s(): void
    {
        $order = $this->makeOrder(true);
        $dish = Dish::findOrFail(1);
        $item1 = OrderItem::create(['OrderID' => $order->OrderID, 'DishID' => $dish->DishID, 'Quantity' => 1, 'ItemStatus' => 'R', 'Choice' => 'Regular']);
        $item2 = OrderItem::create(['OrderID' => $order->OrderID, 'DishID' => $dish->DishID, 'Quantity' => 1, 'ItemStatus' => 'R', 'Choice' => 'Regular']);

        Livewire::test(Orders::class)->call('completeOrder', $order->OrderID);

        $this->assertFalse((bool) $order->fresh()->OrderStatus);

        // Documents a real quirk: item status is reset back to 'S', the
        // same letter used for a brand-new pending item, so a completed
        // order's items look indistinguishable from a fresh order's at
        // the item-status level alone.
        $this->assertSame('S', $item1->fresh()->ItemStatus);
        $this->assertSame('S', $item2->fresh()->ItemStatus);
    }

    public function test_complete_order_closes_the_active_popup(): void
    {
        $order = $this->makeOrder(true);
        $dish = Dish::findOrFail(1);
        OrderItem::create(['OrderID' => $order->OrderID, 'DishID' => $dish->DishID, 'Quantity' => 1, 'ItemStatus' => 'R', 'Choice' => 'Regular']);

        Livewire::test(Orders::class)
            ->call('openOrder', $order->OrderID)
            ->assertSet('activeOrderId', $order->OrderID)
            ->call('completeOrder', $order->OrderID)
            ->assertSet('activeOrderId', null);
    }

    // ── Availability ─────────────────────────────────────────────────

    public function test_toggle_availability_flips_the_flag(): void
    {
        $dish = Dish::findOrFail(1); // seeded Availability = true

        Livewire::test(Availability::class)->call('toggleAvailability', $dish->DishID);
        $this->assertFalse((bool) $dish->fresh()->Availability);

        Livewire::test(Availability::class)->call('toggleAvailability', $dish->DishID);
        $this->assertTrue((bool) $dish->fresh()->Availability);
    }

    public function test_availability_category_filter(): void
    {
        $dishes = Livewire::test(Availability::class)
            ->call('selectCategory', 3) // Drinks
            ->viewData('dishes');

        $this->assertTrue($dishes->every(fn ($d) => $d->CategoryID === 3));
    }
}