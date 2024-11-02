<?php

namespace App\Http\Controllers;


use stdClass;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;



class OrderController extends Controller
{
    public function store(Request $request)
    {
        $token = $request->bearerToken();
        $totalPrice = 0;
        $items = collect($request->get('items'));
        $orderData =[];

        $order = new Order([
            'user_id' => 3, // Assurez-vous de récupérer l'utilisateur réel ou de gérer l'authentification correctement
            'total_price' => 0, // Initialiser à 0, recalculé plus bas
            'status' => 'pending'
        ]);
        $order->save();  // Sauvegarder l'ordre initial pour générer un ID d'ordre

        // Vérifier les produits, calculer le total et sauvegarder les détails de chaque item
        $items->each(function ($item) use (&$totalPrice, &$orderData, $order, $token) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get("http://127.0.0.1:8000/api/products/{$item['product_id']}");

            if ($response->successful()) {
                $product = $response->json();
                $itemPrice = $product['price'] * $item['quantity'];
                $totalPrice += $itemPrice;

                $orderItem = $order->orderItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product['price']
                ]);

                // Préparer les données pour RabbitMQ pour chaque article
                $orderData[] = [
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ];

                // Envoyer les données à RabbitMQ pour chaque article

            }
        });
        Queue::connection('rabbitmq')->push('App\Jobs\UpdateProductStock', [
            'data' => $orderData  // Encapsulez vos données dans une clé 'data'
        ]);

        // Mettre à jour le total_price de la commande après tous les calculs
        $order->total_price = $totalPrice;
        $order->save(); // Sauvegarder à nouveau pour mettre à jour le total

        return response()->json(['order' => $order->load('orderItems')], 201);
    }


    public function show($id)
    {
        $order = Order::with('orderItems')->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update($request->all());
        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
