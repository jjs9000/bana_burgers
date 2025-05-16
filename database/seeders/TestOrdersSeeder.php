<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class TestOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get all available products and users
        $products = Product::with('variations')->get();
        if ($products->isEmpty()) {
            $this->command->error('No products found in the database. Please seed products first.');
            return;
        }

        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->error('No users found in the database. Please seed users first.');
            return;
        }

        $statuses = ['pending', 'preparing', 'completed', 'cancelled'];
        $deliveryStatuses = [null, 'pending', 'in_progress', 'delivered', 'cancelled'];

        $this->command->info('Starting to seed 400 test orders...');
        $progressBar = $this->command->getOutput()->createProgressBar(400);
        $progressBar->start();

        DB::beginTransaction();

        try {
            // Generate orders for the past 90 days
            for ($i = 0; $i < 400; $i++) {
                // Create order with random user or null for guest order
                $user = $faker->boolean(80) ? $users->random() : null;
                $randomDays = $faker->numberBetween(0, 90);
                $orderDate = Carbon::now()->subDays($randomDays)->subMinutes($faker->numberBetween(1, 1440));

                // Random status, with higher probability for completed orders
                $status = $faker->randomElement([
                    'completed',
                    'completed',
                    'completed', // Higher weight for completed
                    'pending',
                    'pending',                 // Medium weight for pending
                    'preparing',                          // Lower weight for preparing
                    'cancelled'                           // Lower weight for cancelled
                ]);

                // Only set delivery time and status for some orders
                $hasDelivery = $faker->boolean(60);
                $deliveryTime = $hasDelivery ? $orderDate->copy()->addHours($faker->numberBetween(1, 24)) : null;
                $deliveryStatus = $hasDelivery ? $faker->randomElement($deliveryStatuses) : null;

                // For completed orders older than 3 days, ensure delivery status is delivered or null
                if ($status === 'completed' && $randomDays > 3 && $hasDelivery) {
                    $deliveryStatus = $faker->randomElement(['delivered', null]);
                }

                // For cancelled orders, ensure delivery status is cancelled or null
                if ($status === 'cancelled' && $hasDelivery) {
                    $deliveryStatus = $faker->randomElement(['cancelled', null]);
                }

                // Create customer data
                $customerName = $user ? $user->name : $faker->name();
                $customerPhone = $faker->boolean(80) ? $faker->phoneNumber() : null;
                $deliveryAddress = $hasDelivery ? $faker->address() : null;

                // Generate unique 3-digit display ID
                $displayId = $this->generateUniqueDisplayId();

                // Create the order
                $order = new Order();
                $order->user_id = $user ? $user->id : 1; // Default to user 1 if no user
                $order->status = $status;
                $order->display_id = $displayId;
                $order->customer_name = $customerName;
                $order->customer_phone = $customerPhone;
                $order->delivery_address = $deliveryAddress;
                $order->delivery_time = $deliveryTime;
                $order->delivery_status = $deliveryStatus;
                $order->created_at = $orderDate;
                $order->updated_at = $orderDate;
                $order->save();

                // Add 1-5 items to the order
                $numItems = $faker->numberBetween(1, 5);
                $orderTotal = 0;

                // Use a subset of products for this order
                $orderProducts = $products->random(min($numItems, $products->count()));

                foreach ($orderProducts as $product) {
                    $quantity = $faker->numberBetween(1, 3);
                    $variation = null;
                    $variationId = null;

                    // If product has variations, randomly select one
                    if ($product->has_variations && $product->variations->count() > 0) {
                        $variation = $product->variations->random();
                        $variationId = $variation->id;
                    }

                    // Calculate unit price and subtotal
                    $unitPrice = $product->price;
                    if ($variation) {
                        $unitPrice += $variation->additional_price;
                    }

                    // Random options as JSON
                    $options = [];
                    if ($faker->boolean(70)) {
                        $possibleOptions = [
                            ['name' => 'Onion', 'type' => 'extra', 'price' => 0],
                            ['name' => 'Onion', 'type' => 'less', 'price' => 0],
                            ['name' => 'Onion', 'type' => 'no', 'price' => 0],
                            ['name' => 'Lettuce', 'type' => 'extra', 'price' => 0],
                            ['name' => 'Lettuce', 'type' => 'less', 'price' => 0],
                            ['name' => 'Lettuce', 'type' => 'no', 'price' => 0],
                            ['name' => 'Cheese', 'type' => 'extra', 'price' => 2],
                            ['name' => 'Sauce', 'type' => 'extra', 'price' => 1],
                        ];

                        // Add 0-3 random options
                        $numOptions = $faker->numberBetween(0, 3);
                        for ($j = 0; $j < $numOptions; $j++) {
                            $option = $faker->randomElement($possibleOptions);
                            $options[] = $option;
                            $unitPrice += $option['price'];
                        }
                    }

                    $subtotal = $unitPrice * $quantity;
                    $orderTotal += $subtotal;

                    // Create the order item
                    $orderItem = new OrderItem();
                    $orderItem->order_id = $order->id;
                    $orderItem->product_id = $product->id;
                    $orderItem->variation_id = $variationId;
                    $orderItem->quantity = $quantity;
                    $orderItem->unit_price = $unitPrice;
                    $orderItem->subtotal = $subtotal;
                    $orderItem->options = $options;
                    $orderItem->notes = $faker->boolean(30) ? $faker->sentence() : null;
                    $orderItem->created_at = $orderDate;
                    $orderItem->updated_at = $orderDate;
                    $orderItem->save();
                }

                // Update order total
                $order->total = $orderTotal;
                $order->save();

                $progressBar->advance();
            }

            DB::commit();
            $progressBar->finish();
            $this->command->info("\nSuccessfully seeded 400 test orders!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("\nAn error occurred while seeding orders: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a unique 3-digit display ID for orders
     */
    private function generateUniqueDisplayId()
    {
        $isUnique = false;
        $displayId = null;

        while (!$isUnique) {
            // Generate a random 3-digit number between 100 and 999
            $displayId = rand(100, 999);

            // Check if it's already in use
            $exists = Order::where('display_id', $displayId)->exists();

            if (!$exists) {
                $isUnique = true;
            }
        }

        return $displayId;
    }
}
