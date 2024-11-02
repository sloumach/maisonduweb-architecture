<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $order_id = $request->order_id;
        $amount = $request->amount;

        // Simuler une validation de paiement
        if ($this->validatePayment($amount)) {
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully.',
                'order_id' => $order_id,
                'amount' => $amount
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed. Insufficient funds.'
            ]);
        }
    }

    private function validatePayment($amount)
    {
        // Simuler la validation du paiement
        return $amount > 0; // Assurez-vous que le montant est positif
    }
}
