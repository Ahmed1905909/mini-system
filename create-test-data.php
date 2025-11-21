<?php

// Simple script to create test data
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Hash;

echo "Creating test data...\n\n";

// Create user if not exists
$user = User::firstOrCreate(
    ['email' => 'demo@test.com'],
    [
        'name' => 'Demo User',
        'password' => Hash::make('password123')
    ]
);
echo "✓ User created: {$user->email}\n";

// Create products
$products = [
    ['name' => 'Laptop', 'description' => 'High-performance laptop', 'price' => 999.99, 'stock' => 10],
    ['name' => 'Mouse', 'description' => 'Wireless mouse', 'price' => 29.99, 'stock' => 50],
    ['name' => 'Keyboard', 'description' => 'Mechanical keyboard', 'price' => 79.99, 'stock' => 25],
    ['name' => 'Monitor', 'description' => '27-inch 4K monitor', 'price' => 399.99, 'stock' => 15],
];

foreach ($products as $productData) {
    Product::firstOrCreate(
        ['name' => $productData['name']],
        $productData
    );
}
echo "✓ Products created\n";

// Create an order
$product1 = Product::where('name', 'Laptop')->first();
$product2 = Product::where('name', 'Mouse')->first();

if ($product1 && $product2) {
    // Decrease stock
    $product1->stock -= 2;
    $product1->save();
    
    $product2->stock -= 3;
    $product2->save();
    
    // Create order
    $order = Order::create([
        'user_id' => $user->id,
        'address' => '123 Main Street, New York, NY 10001',
        'phone' => '+1 555-0100',
        'total' => ($product1->price * 2) + ($product2->price * 3),
        'status' => 'pending'
    ]);
    
    // Create order items
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product1->id,
        'quantity' => 2,
        'price' => $product1->price
    ]);
    
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product2->id,
        'quantity' => 3,
        'price' => $product2->price
    ]);
    
    echo "✓ Order #{$order->id} created\n";
    echo "  - 2x Laptop @ \${$product1->price} each\n";
    echo "  - 3x Mouse @ \${$product2->price} each\n";
    echo "  - Total: \${$order->total}\n";
}

echo "\n✅ Test data created successfully!\n";
echo "\nYou can now:\n";
echo "1. Login with: demo@test.com / password123\n";
echo "2. View products in the Products page\n";
echo "3. View the order in the Orders page\n";
