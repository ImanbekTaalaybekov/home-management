<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentController extends Controller
{
    public function createPayment()
    {
        $user = Auth::user();
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => "7.00"
                    ]
                ]
            ],
            "application_context" => [
                "return_url" => route('payment.success'),
                "cancel_url" => route('payment.cancel')
            ]
        ]);

        if (isset($response['id']) && $response['status'] === 'CREATED') {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return response()->json([
                        'payment_url' => $link['href']
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Ошибка при создании платежа'
        ], 500);
    }

    public function success(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();

        $response = $provider->capturePaymentOrder($request->query('token'));

        if (isset($response['status']) && $response['status'] === 'COMPLETED') {
            $user = Auth::user();
            $user->status = 'vip';
            $user->save();

            return response()->json([
                'message' => 'Оплата прошла успешно! Теперь вы VIP.'
            ]);
        }

        return response()->json([
            'message' => 'Ошибка при обработке платежа'
        ], 500);
    }

    public function cancel()
    {
        return response()->json([
            'message' => 'Оплата была отменена'
        ], 400);
    }
}
