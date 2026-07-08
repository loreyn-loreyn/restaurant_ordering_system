<?php

namespace Tests\Feature;

use App\Livewire\Cashier\Cart;
use App\Livewire\Cashier\DishDetail;
use App\Livewire\Cashier\Menu;
use App\Livewire\Cashier\OrderType;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashierOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->cashier = User::where('UserName', 'C002')->firstOrFail();
        $this->actingAs($this->cashier);
    }

    // ── OrderType ────────────────────────────────────────────────────

    public function test_selecting_order_type_stores_session_and_redirects_to_menu(): void
    {
        Livewire::test(OrderType::class)
            ->call('selectType', 'Dine-in')
            ->assertRedirect(route('cashier.dishes'));

        $this->assertSame('Dine-in', session('current_order_type'));
    }

    public function test_selecting_order_type_discards_previous_uncommitted_order(): void
    {
        $existing = Order::create([
            'UserID' => $this->cashier->UserID,
            'PaymentID' => null,
            'DiscountID' => null,
            'OrderType' => true,
            'OrderStatus' => false,
            'OrderDate' => now()->toDateString(),
            'TotalAmount' => 0,
            'Change' => 0,
        ]);
        session(['current_order_id' => $existing->OrderID]);

        Livewire::test(OrderType::class)->call('selectType', 'Take-out');

        $this->assertDatabaseMissing('orders', ['OrderID' => $existing->OrderID]);
    }

    // ── Menu ─────────────────────────────────────────────────────────

    public function test_menu_redirects_to_order_type_if_none_selected(): void
    {
        Livewire::test(Menu::class)
            ->assertRedirect(route('cashier.order-type'));
    }

    public function test_menu_lists_dishes_available_first(): void
    {
        session(['current_order_type' => 'Dine-in']);

        $dishes = Livewire::test(Menu::class)->viewData('dishes');

        // Seeded DishID 5 (Bottled Water) is Availability => 0, so it
        // should never be first while any available dish exists.
        $this->assertNotSame(5, $dishes->first()->DishID);
    }

    public function test_menu_category_filter(): void
    {
        session(['current_order_type' => 'Dine-in']);

        $dishes = Livewire::test(Menu::class)
            ->call('selectCategory', 1) // Main Dish
            ->viewData('dishes');

        $this->assertTrue($dishes->every(fn ($d) => $d->CategoryID === 1));
    }

    // ── DishDetail ───────────────────────────────────────────────────

    public function test_dish_detail_redirects_if_no_order_type_in_session(): void
    {
        $dish = Dish::findOrFail(1);

        Livewire::test(DishDetail::class, ['dish' => $dish])
            ->assertRedirect(route('cashier.order-type'));
    }

    public function test_add_to_cart_stores_item_in_session(): void
    {
        session(['current_order_type' => 'Dine-in']);
        $dish = Dish::findOrFail(1); // Beef Sisig, no choices configured

        Livewire::test(DishDetail::class, ['dish' => $dish])
            ->set('quantity', 2)
            ->set('specialInstruction', 'No chili please')
            ->call('addToCart')
            ->assertRedirect(route('cashier.dishes'));

        $items = session('cart_items', []);
        $this->assertCount(1, $items);
        $this->assertSame(1, $items[0]['dish_id']);
        $this->assertSame(2, $items[0]['quantity']);
        $this->assertSame('No chili please', $items[0]['special_instruction']);
    }

    public function test_add_to_cart_merges_identical_lines(): void
    {
        session(['current_order_type' => 'Dine-in']);
        $dish = Dish::findOrFail(1);

        Livewire::test(DishDetail::class, ['dish' => $dish])
            ->set('quantity', 1)
            ->call('addToCart');

        Livewire::test(DishDetail::class, ['dish' => $dish])
            ->set('quantity', 3)
            ->call('addToCart');

        $items = session('cart_items', []);
        $this->assertCount(1, $items, 'Identical dish/choice/instruction lines should merge into one.');
        $this->assertSame(4, $items[0]['quantity']);
    }

    public function test_editing_an_existing_cart_line_updates_it_in_place(): void
    {
        session(['current_order_type' => 'Dine-in']);
        $dish = Dish::findOrFail(1);

        Livewire::test(DishDetail::class, ['dish' => $dish])
            ->set('quantity', 1)
            ->call('addToCart');

        $key = session('cart_items')[0]['key'];

        // DishDetail::$cartItem is a #[Url]-bound public property, so we
        // pass it as a normal mount parameter here rather than faking a
        // query string on the request.
        Livewire::test(DishDetail::class, ['dish' => $dish, 'cartItem' => $key])
            ->set('quantity', 5)
            ->call('addToCart');

        $items = session('cart_items');
        $this->assertCount(1, $items, 'Editing an existing line should not create a duplicate.');
        $this->assertSame(5, $items[0]['quantity']);
    }

    // ── Cart ─────────────────────────────────────────────────────────

    public function test_cart_redirects_if_no_order_type_in_session(): void
    {
        Livewire::test(Cart::class)
            ->assertRedirect(route('cashier.order-type'));
    }

    public function test_cart_increment_decrement_and_remove(): void
    {
        session(['current_order_type' => 'Dine-in']);
        $this->putCartItem(dishId: 1, price: 180.00, quantity: 1);
        $key = session('cart_items')[0]['key'];

        $component = Livewire::test(Cart::class);

        $component->call('increment', $key);
        $this->assertSame(2, session('cart_items')[0]['quantity']);

        $component->call('decrement', $key);
        $this->assertSame(1, session('cart_items')[0]['quantity']);

        $component->call('decrement', $key);
        $this->assertCount(0, session('cart_items'), 'Decrementing past 1 removes the line.');
    }

    public function test_cart_subtotal_and_discount_math(): void
    {
        session(['current_order_type' => 'Dine-in']);
        $this->putCartItem(dishId: 1, price: 180.00, quantity: 2); // 360.00

        $component = Livewire::test(Cart::class);
        $this->assertEquals(360.00, $component->get('subtotal'));

        // DiscountID 1 = Senior Citizen, Amount 20.00
        $component->set('discountId', 1);
        $this->assertEquals(340.00, $component->get('totalAfterDiscount'));
    }

    public function test_proceed_payment_cash_insufficient_shows_error(): void
    {
        session(['current_order_type' => 'Dine-in']);
        $this->putCartItem(dishId: 1, price: 180.00, quantity: 1);

        Livewire::test(Cart::class)
            ->set('paymentType', 'Cash')
            ->set('renderedAmount', '50')
            ->call('proceedPayment')
            ->assertHasErrors('renderedAmount');

        $this->assertDatabaseCount('orders', 3); // only the 3 seeded orders
    }

    public function test_proceed_payment_cash_creates_order_items_and_payment(): void
    {
        session(['current_order_type' => 'Take-out']);
        $this->putCartItem(dishId: 1, price: 180.00, quantity: 2, choice: 'Regular', instruction: 'No chili');

        Livewire::test(Cart::class)
            ->set('paymentType', 'Cash')
            ->set('renderedAmount', '400')
            ->call('proceedPayment')
            ->assertSet('paymentSuccessful', true);

        $order = Order::latest('OrderID')->first();

        $this->assertNotNull($order);
        $this->assertFalse((bool) $order->OrderType, 'Take-out should store OrderType as false.');
        $this->assertTrue((bool) $order->OrderStatus, 'A freshly paid order is still pending in the kitchen, so OrderStatus should be true.');
        $this->assertEquals(360.00, $order->TotalAmount);
        $this->assertEquals(40.00, $order->Change);

        $this->assertDatabaseHas('order_items', [
            'OrderID' => $order->OrderID,
            'DishID' => 1,
            'Quantity' => 2,
            'ItemStatus' => 'S',
        ]);

        $payment = Payment::where('OrderID', $order->OrderID)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('Cash', $payment->Method);
        $this->assertEquals(400.00, $payment->RenderedAmount);

        // Circular FK: orders.PaymentID must point back to the payment
        // that was created *after* the order itself.
        $this->assertEquals($payment->PaymentID, $order->fresh()->PaymentID);

        // Staff on the payment should be the acting cashier's own StaffID.
        $this->assertEquals('C002', $payment->StaffID);
    }

    public function test_finish_transaction_clears_session_and_redirects(): void
    {
        session(['current_order_type' => 'Dine-in']);
        $this->putCartItem(dishId: 1, price: 180.00, quantity: 1);

        Livewire::test(Cart::class)
            ->call('finishTransaction')
            ->assertRedirect(route('cashier.order-type'));

        $this->assertNull(session('cart_items'));
        $this->assertNull(session('current_order_type'));
    }

    /**
     * Helper: push a single line directly into the session cart the same
     * shape DishDetail::saveItem() produces, without going through the
     * component (keeps discount/payment tests focused).
     */
    protected function putCartItem(int $dishId, float $price, int $quantity, ?string $choice = null, ?string $instruction = null): void
    {
        session(['cart_items' => [[
            'key' => uniqid(),
            'dish_id' => $dishId,
            'dish_name' => 'Test Dish',
            'photo_url' => null,
            'price' => $price,
            'quantity' => $quantity,
            'choice' => $choice,
            'special_instruction' => $instruction,
        ]]]);
    }
}