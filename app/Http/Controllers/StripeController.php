<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;

class StripeController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function updatePaymentMethod()
    {
        return view('update-payment-method', [
            'intent' => auth()->user()->createSetupIntent()
        ]);
    }

    public function checkout(Request $request)
    {
        $user = $request->user();

        // save user info
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $email = $request->email;
        $phone = $request->phone;
        $customer_id = $user->stripe_id;
        if (!$user->stripe_id) {
            $customer = Customer::create([
                'email' => $email,
                'name' => $first_name . ' ' . $last_name,
                'phone' => $phone,
                'source' => $request->input('stripeToken'),
            ]);
            $user->stripe_id = $customer->id;
            $user->save();
            $customer_id = $customer->id;
        }

        // payment method
        $paymentMethods = Customer::allPaymentMethods($customer_id);
        $paymentMethod = $paymentMethods->data[0]->id; //card

        // setup for a session payment
        $intent = PaymentIntent::create([
            'amount' => 1099,
            'currency' => 'usd',
            'customer' => $customer_id,
            'payment_method' => $paymentMethod
        ]);

        // confirm intent
        $confirm = $intent->confirm(
            [
                'payment_method' => $paymentMethod,
                'return_url' => route('checkout-success'),
            ]
        );

        // todo: store the intents for check payment logs later

        if ($confirm->status == 'succeeded') {
            // send to customer
            $customer = Customer::retrieve($customer_id);
            $this->sendmail($customer);

            // todo: send to admin same as customer

            return redirect()->route('checkout-success');
        }

        return redirect()->route('checkout-cancel');
    }

    public function sendmail($customer)
    {
        $subject = "Payment Success!";
        $email = $customer->email;
        $mailer['from'] = env('MAIL_FROM_ADDRESS');
        \Mail::raw('Test content', function ($message) use ($email, $subject, $mailer) {
            $message->from($mailer['from']);
            $message->to($email)->subject($subject);
        });
    }
}
