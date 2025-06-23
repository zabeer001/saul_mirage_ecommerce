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
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function checkout(Request $request)
    {
        // Validate request data
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'email' => 'required|email',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Create Stripe Checkout session
        $session = Session::create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'aed',
                        'product_data' => [
                            'name' => 'Product'
                        ],
                        'unit_amount' => round((float) $order->total * 100),
                    ],
                    'quantity' => 1
                ]
            ],
            'mode' => 'payment',
            'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe.cancel'),
            'customer_email' => $request->email, // Add customer email
            'metadata' => [ // Add order_id to metadata
                'order_id' => $order->id
            ]

        ]);

        // Save the session ID to the order
        $order->update(['session_id' => $session->id]);

        return response()->json([
            'status' => 'success',
            'checkout_url' => $session->url
        ]);
    }

    // public function webhook(Request $request)
    // {
    //     $payload = $request->getContent();
    //     $sig_header = $request->header('Stripe-Signature');
    //     $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

    //     try {
    //         $event = \Stripe\Webhook::constructEvent(
    //             $payload, $sig_header, $endpoint_secret
    //         );
    //     } catch (\UnexpectedValueException $e) {
    //         return response()->json(['error' => 'Invalid payload'], 400);
    //     } catch (\Stripe\Exception\SignatureVerificationException $e) {
    //         return response()->json(['error' => 'Invalid signature'], 400);
    //     }

    //     // Handle the checkout.session.completed event
    //     if ($event->type == 'checkout.session.completed') {
    //         $session = $event->data->object;

    //         // Update order status
    //         $order = Order::where('session_id', $session->id)->first();
    //         if ($order) {
    //             $order->update(['payment_status' => 'paid']);
    //         }
    //     }

    //     return response()->json(['status' => 'success']);
    // }

    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'session_id' => 'required'
        ]);

        $session = Session::retrieve($request->session_id);

        return response()->json([
            'payment_status' => $session->payment_status,
            'order_status' => $session->payment_status === 'paid' ? 'completed' : 'pending'
        ]);
    }


    public function checkoutSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');
        $frontendUrl = env('FRONTEND_URL');

        if (!$sessionId) {
            return redirect($frontendUrl . '/payment/canceled');
        }

        try {
            $checkoutSession = Session::retrieve($sessionId);
            $orderId = $checkoutSession->metadata->order_id;
            $order_id = (int) $orderId;

            if ($checkoutSession->payment_status === 'paid') {
                $order = Order::find($order_id);
                if ($order) {
                    $order->update(['payment_status' => 'paid']);
                    return redirect($frontendUrl . '/payment/success');
                } else {
                    return redirect($frontendUrl . '/payment/canceled');
                }
            } else {
                return redirect($frontendUrl . '/payment/canceled');
            }
        } catch (\Exception $e) {
            return redirect($frontendUrl . '/payment/canceled');
        }
    }

    
    public function checkoutCancel()
    {
        $frontendUrl = env('FRONTEND_URL');
        return redirect($frontendUrl . '/payment/canceled');
    }
}
