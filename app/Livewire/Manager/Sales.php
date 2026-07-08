<?php

namespace App\Livewire\Manager;

use App\Models\Order;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.manager')]
class Sales extends Component
{
    public string $period = 'daily'; // daily | weekly | monthly | yearly

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    protected function dateRange(): array
    {
        $now = Carbon::now();

        return match ($this->period) {
            'daily' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            'weekly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'monthly' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'yearly' => [$now->copy()->subYears(4)->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }

    protected function buildBuckets($orders, Carbon $start, Carbon $end): array
    {
        $buckets = [];

        switch ($this->period) {
            case 'daily':
                $cursor = $start->copy();
                while ($cursor->lte($end)) {
                    $buckets[$cursor->format('D')] = 0.0;
                    $cursor->addDay();
                }
                foreach ($orders as $order) {
                    $key = Carbon::parse($order->OrderDate)->format('D');
                    $buckets[$key] = ($buckets[$key] ?? 0) + (float) $order->TotalAmount;
                }
                break;

            case 'weekly':
                $cursor = $start->copy();
                $weekNum = 1;
                while ($cursor->lte($end)) {
                    $buckets['Wk ' . $weekNum] = 0.0;
                    $cursor->addWeek();
                    $weekNum++;
                }
                foreach ($orders as $order) {
                    $weekIndex = (int) $start->diffInWeeks(Carbon::parse($order->OrderDate)) + 1;
                    $key = 'Wk ' . $weekIndex;
                    if (isset($buckets[$key])) {
                        $buckets[$key] += (float) $order->TotalAmount;
                    }
                }
                break;

            case 'monthly':
                for ($m = 1; $m <= 12; $m++) {
                    $buckets[Carbon::create()->month($m)->format('M')] = 0.0;
                }
                foreach ($orders as $order) {
                    $key = Carbon::parse($order->OrderDate)->format('M');
                    $buckets[$key] += (float) $order->TotalAmount;
                }
                break;

            case 'yearly':
                for ($y = $start->year; $y <= $end->year; $y++) {
                    $buckets[(string) $y] = 0.0;
                }
                foreach ($orders as $order) {
                    $key = (string) Carbon::parse($order->OrderDate)->year;
                    if (isset($buckets[$key])) {
                        $buckets[$key] += (float) $order->TotalAmount;
                    }
                }
                break;
        }

        return $buckets;
    }

    public function render()
    {
        [$start, $end] = $this->dateRange();

        $orders = Order::with(['items.dish.category', 'discount'])
            ->where('OrderStatus', true)
            ->whereBetween('OrderDate', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->get();

        $totalSales = (float) $orders->sum('TotalAmount');
        $orderCount = $orders->count();
        $averageCheck = $orderCount > 0 ? $totalSales / $orderCount : 0;

        $buckets = $this->buildBuckets($orders, $start, $end);
        $maxBucket = ! empty($buckets) ? max(1, max($buckets)) : 1;

        $categorySales = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $catName = $item->dish->category->CategoryName ?? 'Uncategorized';
                $categorySales[$catName] = ($categorySales[$catName] ?? 0) + $item->line_total;
            }
        }
        arsort($categorySales);

        $itemCounts = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $name = $item->dish->DishName ?? 'Unknown';
                $itemCounts[$name] = ($itemCounts[$name] ?? 0) + $item->Quantity;
            }
        }
        arsort($itemCounts);
        $topItems = array_slice($itemCounts, 0, 5, true);

        $discountOrders = $orders->filter(fn ($o) => $o->discount)->values();

        return view('livewire.manager.sales', [
            'totalSales' => $totalSales,
            'orderCount' => $orderCount,
            'averageCheck' => $averageCheck,
            'buckets' => $buckets,
            'maxBucket' => $maxBucket,
            'categorySales' => $categorySales,
            'topItems' => $topItems,
            'discountOrders' => $discountOrders,
        ]);
    }
}