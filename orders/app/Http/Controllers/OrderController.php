<?php

namespace App\Http\Controllers;


use stdClass;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    $productIds = $items->pluck('product_id')->unique();

    try {
        DB::transaction(function () use ($items, $productIds, $token, &$totalPrice, &$orderData) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->post("http://127.0.0.1:8000/api/products/batch", ['ids' => $productIds->toArray()]);

            if ($response->successful()) {
                $products = collect($response->json());
                $order = new Order([
                    'user_id' => 3,
                    'total_price' => 0,
                    'status' => 'pending'
                ]);
                $order->save();

                $items->each(function ($item) use ($products, &$totalPrice, $order) {
                    $product = $products->firstWhere('id', $item['product_id']);
                    if ($product['quantity'] == 0) {
                        throw new \Exception('Product unavailable');
                    }
                    $itemPrice = $product['price'] * $item['quantity'];
                    $totalPrice += $itemPrice;

                    $order->orderItems()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $product['price']
                    ]);

                    $orderData[] = [
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ];
                });

                Queue::connection('rabbitmq')->push('App\Jobs\UpdateProductStock', [
                    'data' => $orderData,
                    'action' => 'decrement',
                ]);

                $order->total_price = $totalPrice;
                $order->save();
            }
        });
        return response()->json(['order' => $order->load('orderItems')], 201);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
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
