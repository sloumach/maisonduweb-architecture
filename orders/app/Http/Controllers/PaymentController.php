<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $order_id = $request->order_id;
        $amount = $request->amount;

        // Vérifier la présence et la validité de l'ordre
        $order = Order::findOrFail($order_id);

        try {
            DB::transaction(function () use ($amount, $order) {
                if ($this->validatePayment($amount)) {
                    $order->status = 'done';
                    $order->save();
                    // Ajouter ici toute autre logique nécessaire après la validation du paiement
                } else {
                    // Logique d'échec de paiement
                    $this->handleFailedPayment($order);
                }
            });

            return response()->json([
                'success' => $order->status === 'done',
                'message' => $order->status === 'done' ? 'Payment processed successfully.' : 'Payment failed. Insufficient funds.',
                'order_id' => $order->id,
                'amount' => $amount
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Transaction failed: ' . $e->getMessage()], 500);
        }
    }

    private function handleFailedPayment($order)
    {
        foreach ($order->orderItems as $item) {
            Queue::connection('rabbitmq')->push('App\Jobs\UpdateProductStock', [
                'data' => $item,
                'action' => 'increment',
            ]);
        }

        $order->status = 'payment_failed';
        $order->save();
    }



    private function validatePayment($amount)
    {
        // Simuler la validation du paiement
        return $amount > 0; // Assurez-vous que le montant est positif
    }

}
