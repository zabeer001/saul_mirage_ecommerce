<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET')); // Set Stripe secret key
    }

    public function checkout(Request $request)
    {
        // Validate request data
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);
        $user = auth()->user();

        // Create line items for Stripe Checkout
        $lineItems = [
            [
                'price_data' => [
                    'currency' => env('CASHIER_CURRENCY', 'usd'),
                    'unit_amount' => (float) $order->total * 100, // Convert total to cents
                ],
            ],
        ];

        // Create Stripe Checkout session
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'customer_email' => $user ? $user->email : $request->input('email'),
            'mode' => 'payment',
            'success_url' => route('stripe-success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe-cancel'),
            'metadata' => ['order_id' => $order->id],
        ]);

        return response()->json(['payment_url' => $checkoutSession->url]);
    }

    public function checkoutSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');
        $checkoutSession = Session::retrieve($sessionId);

        if ($checkoutSession->payment_status === 'paid') {
            // Update order status to paid
            $order = Order::where('session_id', $sessionId)->first();
            if ($order) {
                $order->update(['payment_status' => 'paid']);
            }

            return redirect('/')->with('message', 'Payment successful!');
        }

        return redirect('/')->with('error', 'Payment not completed.');
    }

    public function checkoutCancel()
    {
        return redirect('/')->with('error', 'Payment was canceled.');
    }
}
