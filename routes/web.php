<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
use Illuminate\Http\Request;
use App\Http\Controllers\StripeController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/update-payment-method', [StripeController::class, 'updatePaymentMethod']);
    Route::post('/checkout', [StripeController::class, 'checkout'])->name('checkout');
    Route::view('checkout-success', 'checkout.success')->name('checkout-success');
    Route::view('checkout-cancel','checkout.cancel')->name('checkout-cancel');
});

require __DIR__.'/auth.php';
