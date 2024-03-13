<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\PaymentIntent;
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

    Route::get('/update-payment-method', function () {
        return view('update-payment-method', [
            'intent' => auth()->user()->createSetupIntent()
        ]);
    });


    Route::post('/charge', function (Request $request) {
        // Set your Stripe API key.
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));



        $paymentMethods = $request->user()->paymentMethods();

//        dd($paymentMethods[0]->id);

//        $stripe = new \Stripe\StripeClient('sk_test_51OthkqKCpNfNYnbKMoeo9GoyLheU2WC2tt32QK8HUmHR8GeIEwo3AZOeHm7tL998sVMzYsDN5ZBuDIZYH6rTOIAV00JRnxOjLG');
//        $stripe->paymentMethods->attach(
//            'pm_1MqM05LkdIwHu7ixlDxxO6Mc',
//            ['customer' => 'cus_NbZ8Ki3f322LNn']
//        );



        $res = $request->user()->charge(
            100, $paymentMethods[0]->id
        );
        // Create a new Stripe charge.
//        $charge = \Stripe\Charge::create([
//            'customer' => $customer->id,
//            'amount' => $amount,
//            'currency' => 'usd',
//            'source' => 'card',
//        ]);

        // Display a success message to the user.
        route('checkout-success');
    });
    Route::post('/checkout', function (Request $request) {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $user = $request->user();

        // payment method
        $paymentMethods = $user->paymentMethods();
        $paymentMethod = $paymentMethods[0]->id;

        // save user info
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $email = $request->email;
        $phone = $request->phone;
        $customer_id = $user->stripe_id;
        if (!$user->stripe_id) {
            $customer = \Stripe\Customer::create([
                'email' => $email,
                'name' => $first_name . ' ' . $last_name,
                'phone' => $phone,
                'source' => $request->input('stripeToken'),
            ]);
            $customer_id = $customer->id;
        }

        // setup for a session payment
        $intent = PaymentIntent::create([
            'amount' => 1099,
            'currency' => 'usd',
            'customer' => $customer_id,
            'payment_method' => $paymentMethod
        ]);

        // confirm intent
        $confirm = $intent->confirm(
            $intent->id,
            [
                'payment_method' => 'card',
                'return_url' => route('checkout-success'),
            ]
        );
        dd($confirm, $intent->status);

    })->name('checkout');

    Route::view('checkout-success', 'checkout.success')->name('checkout-success');
    Route::view('checkout-cancel','checkout.cancel')->name('checkout-cancel');
});

require __DIR__.'/auth.php';
