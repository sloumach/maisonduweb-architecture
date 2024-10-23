<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Validator;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Product Microservice API",
 *     description="API for managing products in the product microservice",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 * @OA\Server(
 *     description="Main Server",
 *     url="http://localhost:8000/api"
 * )
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Product model",
 *     required={"id", "name", "price"},
 *     properties={
 *         @OA\Property(
 *             property="id",
 *             type="integer",
 *             description="Product ID"
 *         ),
 *         @OA\Property(
 *             property="name",
 *             type="string",
 *             description="Product name"
 *         ),
 *         @OA\Property(
 *             property="description",
 *             type="string",
 *             description="Product description"
 *         ),
 *         @OA\Property(
 *             property="price",
 *             type="number",
 *             format="float",
 *             description="Product price"
 *         )
 *     }
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/products",
     *     operationId="getProductsList",
     *     tags={"Products"},
     *     summary="Get list of products",
     *     description="Returns list of products",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *            type="array",
     *            @OA\Items(ref="#/components/schemas/Product")
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    /**
     * @OA\Post(
     *     path="/products",
     *     operationId="storeProduct",
     *     tags={"Products"},
     *     summary="Store a new product",
     *     description="Stores a new product and returns that product.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="Invalid product data provided.")
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $product = new Product([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price
        ]);
        $product->save();

        return response()->json(['message' => 'Product created successfully', 'data' => $product], 201);
    }

    /**
     * @OA\Get(
     *     path="/products/{id}",
     *     operationId="getProduct",
     *     tags={"Products"},
     *     summary="Get a single product",
     *     description="Returns a single product by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    /**
     * @OA\Put(
     *     path="/products/{id}",
     *     operationId="updateProduct",
     *     tags={"Products"},
     *     summary="Update an existing product",
     *     description="Updates and returns a product.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to update",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Product data to update",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid product data provided.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->update($request->all());
        return response()->json(['message' => 'Product updated successfully', 'data' => $product]);
    }

    /**
     * @OA\Delete(
     *     path="/products/{id}",
     *     operationId="deleteProduct",
     *     tags={"Products"},
     *     summary="Delete a product",
     *     description="Deletes a product by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to delete",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
