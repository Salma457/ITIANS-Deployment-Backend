<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use App\Models\JobPricing; 
class PaymentController extends Controller
{


public function createCheckoutSession(Request $request)
{
    $user = Auth::user();
    Stripe::setApiKey(env('STRIPE_SECRET'));

    // ❗ الحصول على السعر من قاعدة البيانات
    $pricing = JobPricing::latest()->first();
    $amountInDollars = $pricing ? $pricing->price : 3.00; // fallback للسعر القديم لو مفيش قيمة
    $amount = $amountInDollars * 100; // لتحويله لـ سنتات

    $currency = 'usd';

    $session = Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => $currency,
                'product_data' => ['name' => 'Add New Job'],
                'unit_amount' => (int)$amount,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost:5173/employer/post-job',
        'cancel_url' => 'http://localhost:5173/cancel',
        'metadata' => ['user_id' => $user->id],
    ]);

    Payment::create([
        'user_id' => $user->id,
        'stripe_session_id' => $session->id,
        'stripe_payment_intent_id' => $session->payment_intent,
        'amount' => $amountInDollars,
        'currency' => $currency,
        'used_for_job' => false,
    ]);

    return response()->json(['url' => $session->url]);
}




    public function handleStripeWebhook(Request $request)
{
    $payload = @file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

    try {
        $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $paymentIntentId = $session->payment_intent;

            $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();
            if ($payment) {
                
            }
        }

        return response()->json(['status' => 'ok']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}



public function hasUnusedPayment(Request $request)
{
    $user = Auth::user();

    $payment = Payment::where('user_id', $user->id)
                      ->where('used_for_job', false)
                      ->latest()
                      ->first();

    if ($payment) {
        return response()->json([
            'has_payment' => true,
            'session_id' => $payment->stripe_payment_intent_id,
        ]);
    }

    return response()->json(['has_payment' => false]);
}


}
