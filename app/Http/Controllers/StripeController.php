<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Exception;

class StripeController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        // Set your secret key
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        try {
            // Validate request
            $request->validate([
                'amount' => 'required|numeric|min:1',
            ]);

            // Create a PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100, // Convert to cents
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'description' => 'Payment for order',
            ]);

            return response()->json([
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating payment intent: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function confirmPayment(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        try {
            $request->validate([
                'payment_intent_id' => 'required|string',
            ]);

            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            return response()->json([
                'success' => true,
                'status' => $paymentIntent->status,
                'paymentIntent' => $paymentIntent,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error confirming payment: ' . $e->getMessage(),
            ], 400);
        }
    }
}