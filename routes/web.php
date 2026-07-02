<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\UpdatePassword;
use App\Livewire\Manager\Sales as ManagerSales;
use App\Livewire\Manager\Dishes as ManagerDishes;
use App\Livewire\Manager\Staffs as ManagerStaffs;
use App\Livewire\Manager\StaffCreate as ManagerStaffCreate;
use App\Livewire\Manager\StaffDetail as ManagerStaffDetail;
use App\Livewire\Cashier\OrderType;
use App\Livewire\Cashier\Menu;
use App\Livewire\Cashier\DishDetail;
use App\Livewire\Cashier\Cart;
use App\Livewire\Kitchen\Orders as KitchenOrders;
use App\Livewire\Kitchen\Availability as KitchenAvailability;

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.forgot');
    Route::get('/customer-display', \App\Livewire\CustomerDisplay::class)->name('customer.display');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/update-password', UpdatePassword::class)->name('password.update');

    Route::post('/logout', function () {
        \Illuminate\Support\Facades\Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    // Admin
    Route::middleware('role:Admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        Route::get('/users', function () {
            return view('admin.users');
        })->name('admin.users');
    });

    // Manager
    Route::middleware('role:Manager')->prefix('manager')->group(function () {
        Route::get('/sales', ManagerSales::class)->name('manager.sales');
        Route::get('/dishes', ManagerDishes::class)->name('manager.dishes');
        Route::get('/staffs', ManagerStaffs::class)->name('manager.staffs');
        // IMPORTANT: /staffs/create must be registered before /staffs/{staffDetail}
        // or Laravel will try to resolve "create" as a StaffID.
        Route::get('/staffs/create', ManagerStaffCreate::class)->name('manager.staff.create');
        Route::get('/staffs/{staffDetail}', ManagerStaffDetail::class)->name('manager.staff.detail');
    });

    // Cashier
    Route::middleware('role:Cashier')->prefix('cashier')->group(function () {
        Route::get('/order-type', OrderType::class)->name('cashier.order-type');
        Route::get('/dishes', Menu::class)->name('cashier.dishes');
        Route::get('/dishes/{dish}', DishDetail::class)->name('cashier.dish');
        Route::get('/cart', Cart::class)->name('cashier.cart');
    });

    // Kitchen Staff
    Route::middleware('role:Kitchen Staff')->prefix('kitchen')->group(function () {
        Route::get('/orders', KitchenOrders::class)->name('kitchen.orders');
        Route::get('/availability', KitchenAvailability::class)->name('kitchen.availability');
    });
});

Route::get('/', function () {
    return redirect()->route('login');
});