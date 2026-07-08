<?php

namespace Tests\Feature;

use App\Livewire\Manager\Sales;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('UserName', 'M004')->firstOrFail());
    }

    /**
     * Sales.php filters on `OrderStatus = true`. Given how Cart::proceedPayment
     * and Kitchen\Orders::completeOrder actually use that flag elsewhere in
     * the app (true = paid, still in the kitchen queue; false = fully
     * completed), this means the sales report is really only counting
     * orders still awaiting kitchen completion, and drops fully-completed
     * orders from every total below. That's a real reporting bug, but
     * these tests describe current behavior rather than "fixing" it.
     */
    protected function makePaidOrder(string $date, float $total, bool $orderStatus = true): Order
    {
        return Order::create([
            'UserID' => 3,
            'PaymentID' => null,
            'DiscountID' => null,
            'OrderType' => true,
            'OrderStatus' => $orderStatus,
            'OrderDate' => $date,
            'TotalAmount' => $total,
            'Change' => 0,
        ]);
    }

    public function test_totals_only_include_orders_with_orderstatus_true(): void
    {
        // Seeded OrderID 1 & 2 are OrderStatus true (380 + 175 = 555);
        // OrderID 3 is false and must be excluded.
        $totalSales = Livewire::test(Sales::class)->viewData('totalSales');

        $this->assertEquals(555.00, $totalSales);
    }

    public function test_daily_period_buckets_last_seven_days_by_weekday(): void
    {
        $today = Carbon::now();
        $this->makePaidOrder($today->toDateString(), 100.00);

        $buckets = Livewire::test(Sales::class)
            ->call('setPeriod', 'daily')
            ->viewData('buckets');

        $todayLabel = $today->format('D');
        $this->assertArrayHasKey($todayLabel, $buckets);
        $this->assertGreaterThanOrEqual(100.00, $buckets[$todayLabel]);
    }

    public function test_monthly_period_groups_orders_by_month_label(): void
    {
        $janOrder = $this->makePaidOrder(Carbon::now()->startOfYear()->toDateString(), 250.00);

        $buckets = Livewire::test(Sales::class)
            ->call('setPeriod', 'monthly')
            ->viewData('buckets');

        $label = Carbon::now()->startOfYear()->format('M');
        $this->assertArrayHasKey($label, $buckets);
        $this->assertGreaterThanOrEqual(250.00, $buckets[$label]);
    }

    public function test_yearly_period_groups_orders_by_year(): void
    {
        $this->makePaidOrder(Carbon::now()->toDateString(), 400.00);

        $buckets = Livewire::test(Sales::class)
            ->call('setPeriod', 'yearly')
            ->viewData('buckets');

        $label = (string) Carbon::now()->year;
        $this->assertArrayHasKey($label, $buckets);
        $this->assertGreaterThanOrEqual(400.00, $buckets[$label]);
    }

    /**
     * render() scopes the underlying query to whereBetween(startOfMonth(),
     * endOfMonth()) *before* buildBuckets() ever runs, so a previous-month
     * order never reaches the weekly bucket-index logic at all — it's
     * excluded unconditionally at the query stage, every time.
     *
     * Seeded OrderID 1 & 2 (555 total, OrderStatus true) are dated
     * `Carbon::now()->subDays(2)` by DatabaseSeeder. Whether they land in
     * the current month depends purely on today's date (e.g. they roll
     * into last month on the 1st/2nd of the month), so we derive that
     * from the calendar rather than asserting a fixed number.
     */
    public function test_weekly_period_excludes_orders_outside_the_current_month(): void
    {
        $seededOrdersDate = Carbon::now()->subDays(2);
        $seededOrdersInCurrentMonth = $seededOrdersDate->isSameMonth(Carbon::now());
        $expectedBaseline = $seededOrdersInCurrentMonth ? 555.00 : 0.00;

        $lastMonth = Carbon::now()->startOfMonth()->subMonthNoOverflow();
        $this->makePaidOrder($lastMonth->toDateString(), 999.00);

        $buckets = Livewire::test(Sales::class)
            ->call('setPeriod', 'weekly')
            ->viewData('buckets');

        $this->assertEquals($expectedBaseline, array_sum($buckets), 'Previous-month orders should never be counted toward this month\'s weekly buckets.');
    }

    /**
     * Within the current month, buildBuckets() computes the bucket index as
     * `$start->diffInWeeks($orderDate) + 1`. This test checks that an order
     * roughly two weeks into the month lands in a bucket at all (i.e. isn't
     * silently dropped), without asserting exactly which "Wk N" label the
     * current calendar happens to produce. The expected total accounts for
     * seeded OrderID 1 & 2 (555 total) only when their seeded date
     * (`Carbon::now()->subDays(2)`) actually falls in the current month.
     */
    public function test_weekly_period_buckets_an_order_within_the_current_month(): void
    {
        $seededOrdersDate = Carbon::now()->subDays(2);
        $seededOrdersInCurrentMonth = $seededOrdersDate->isSameMonth(Carbon::now());
        $expectedBaseline = $seededOrdersInCurrentMonth ? 555.00 : 0.00;

        $start = Carbon::now()->startOfMonth();
        $midMonthDate = $start->copy()->addDays(14);

        if ($midMonthDate->gt(Carbon::now()->endOfMonth())) {
            $midMonthDate = Carbon::now()->endOfMonth();
        }

        $this->makePaidOrder($midMonthDate->toDateString(), 321.00);

        $buckets = Livewire::test(Sales::class)
            ->call('setPeriod', 'weekly')
            ->viewData('buckets');

        $this->assertEquals($expectedBaseline + 321.00, array_sum($buckets), 'An order within the current month should land in exactly one weekly bucket.');
    }

    public function test_average_check_is_total_sales_divided_by_order_count(): void
    {
        $component = Livewire::test(Sales::class);

        $totalSales = $component->viewData('totalSales');
        $orderCount = $component->viewData('orderCount');
        $averageCheck = $component->viewData('averageCheck');

        $this->assertEquals($orderCount > 0 ? $totalSales / $orderCount : 0, $averageCheck);
    }

    public function test_average_check_is_zero_when_there_are_no_qualifying_orders(): void
    {
        Order::query()->update(['OrderStatus' => false]);

        $averageCheck = Livewire::test(Sales::class)->viewData('averageCheck');

        $this->assertEquals(0, $averageCheck);
    }

    public function test_category_sales_are_summed_from_order_items(): void
    {
        // Seeded OrderID 1: DishID 1 (Main Dish) qty 1 @180 + DishID 4 (Drinks) qty 2 @50 = 280
        // Seeded OrderID 2: DishID 2 (Main Dish) qty 1 @150 + DishID 5 (Drinks) qty 1 @25 = 175
        $categorySales = Livewire::test(Sales::class)->viewData('categorySales');

        $this->assertEquals(330.00, $categorySales['Main Dish']); // 180 + 150
        $this->assertEquals(125.00, $categorySales['Drinks']);    // 100 + 25
    }

    public function test_top_items_counts_quantity_sold_across_qualifying_orders(): void
    {
        $topItems = Livewire::test(Sales::class)->viewData('topItems');

        $this->assertArrayHasKey('Iced Tea', $topItems);
        $this->assertEquals(2, $topItems['Iced Tea']); // OrderItemID 2, qty 2
    }

    public function test_discount_orders_list_only_includes_orders_with_a_discount(): void
    {
        $order = $this->makePaidOrder(now()->toDateString(), 500.00);
        $order->update(['DiscountID' => 1]); // Senior Citizen

        $discountOrders = Livewire::test(Sales::class)->viewData('discountOrders');

        $this->assertTrue($discountOrders->contains('OrderID', $order->OrderID));
        $this->assertFalse($discountOrders->contains('OrderID', 1)); // seeded order has no discount
    }
}