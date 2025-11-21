<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'phone' => 'required|string',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $total = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['id']);

                if ($product->stock < $item['qty']) {
                    DB::rollBack();
                    return response()->json(['message' => "Product {$product->name} is out of stock"], 400);
                }

                $product->stock -= $item['qty'];
                $product->save();

                $total += $product->price * $item['qty'];
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['qty'],
                    'price' => $product->price,
                ];
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'address' => $request->address,
                'phone' => $request->phone,
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            DB::commit();

            // Reload order with relationships for items summary
            $order->load('items.product');

            return response()->json([
                'message' => 'Order created successfully',
                'order_id' => $order->id,
                'total' => $total,
                'items_count' => count($orderItems),
                'items' => $order->items->map(function($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name ?? 'Unknown Product',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->price * $item->quantity
                    ];
                })
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Order creation failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function index()
    {
        $orders = Order::with('items.product')->orderBy('created_at', 'desc')->get();
        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with('items.product')->find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return response()->json($order);
    }
}

