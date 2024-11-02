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


/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Order Microservice API",
 *     description="API for managing orders in the order microservice",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 * @OA\Server(
 *     description="Order API Server",
 *     url="http://localhost:8000/api"
 * )
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     title="Order",
 *     description="Order model",
 *     required={"id", "user_id", "total_price", "status"},
 *     properties={
 *         @OA\Property(
 *             property="id",
 *             type="integer",
 *             description="Order ID"
 *         ),
 *         @OA\Property(
 *             property="user_id",
 *             type="integer",
 *             description="User ID"
 *         ),
 *         @OA\Property(
 *             property="total_price",
 *             type="number",
 *             format="float",
 *             description="Total price of the order"
 *         ),
 *         @OA\Property(
 *             property="status",
 *             type="string",
 *             description="Status of the order"
 *         )
 *     }
 * )
 */

class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/orders",
     *     operationId="storeOrder",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     description="Creates a new order and returns the newly created order.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="Invalid order data provided.")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
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


 /**
     * @OA\Get(
     *     path="/orders/{id}",
     *     operationId="getOrder",
     *     tags={"Orders"},
     *     summary="Get a single order",
     *     description="Returns a single order by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the order to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function show($id)
    {
        $order = Order::with('orderItems')->findOrFail($id);
        return response()->json($order);
    }
    /**
     * @OA\Put(
     *     path="/orders/{id}",
     *     operationId="updateOrder",
     *     tags={"Orders"},
     *     summary="Update an existing order",
     *     description="Updates and returns an order.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the order to update",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Order data to update",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid order data provided.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update($request->all());
        return response()->json($order);
    }
/**
     * @OA\Delete(
     *     path="/orders/{id}",
     *     operationId="deleteOrder",
     *     tags={"Orders"},
     *     summary="Delete an order",
     *     description="Deletes an order by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the order to delete",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
