<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\UpdatePassword;
use App\Livewire\Cashier\OrderType;
use App\Livewire\Cashier\Menu;
use App\Livewire\Cashier\DishDetail;
use App\Livewire\Cashier\Cart;

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.forgot');
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
        Route::get('/sales', function () {
            return view('manager.sales');
        })->name('manager.sales');

        Route::get('/dishes', function () {
            return view('manager.dishes');
        })->name('manager.dishes');

        Route::get('/staffs', function () {
            return view('manager.staffs');
        })->name('manager.staffs');
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
        Route::get('/orders', function () {
            return view('kitchen.orders');
        })->name('kitchen.orders');

        Route::get('/availability', function () {
            return view('kitchen.availability');
        })->name('kitchen.availability');
    });
});

Route::get('/', function () {
    return redirect()->route('login');
});