<?php

namespace App\Livewire\Pos;

use App\Models\Category;
use App\Models\Option;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PosIndex extends Component
{
    public Collection $categories;
    public Collection $products;
    public ?Product $selectedProduct = null;
    public ?int $selectedVariationId = null;
    public array $selectedOptions = [];
    public int $quantity = 1;
    public ?string $notes = null;

    public array $cart = [];
    public float $total = 0;

    // Loading states
    public bool $isLoadingProducts = false;
    public bool $isProcessingOrder = false;

    // Cache expiration time (in seconds)
    private $cacheExpiry = 300; // 5 minutes

    // Customer Information
    public ?string $customerName = null;
    public ?string $customerPhone = null;
    public ?string $deliveryAddress = null;

    // Tab Navigation with state persistence
    #[Url]
    public string $activeTab = 'pos';

    #[Url]
    public ?int $selectedCategoryId = null;

    public function __construct()
    {
        // Initialize categories as an empty collection to prevent null errors
        $this->categories = collect();
        $this->products = collect();
    }

    public function initComponent()
    {
        // This is called by wire:init in the template
        // Lazy load categories
        $this->loadCategories();

        // Load products if category is already selected
        if ($this->selectedCategoryId) {
            $this->loadProductsForCategory($this->selectedCategoryId);
        }
    }

    protected function loadCategories()
    {
        try {
            // Cache categories to avoid repeated database queries
            $this->categories = Cache::remember('pos_categories', $this->cacheExpiry, function () {
                return Category::select(['id', 'name'])->orderBy('name')->get();
            });

            Log::info('Categories loaded: ' . $this->categories->count());
        } catch (\Exception $e) {
            Log::error('Error loading categories: ' . $e->getMessage());
            $this->categories = collect();
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Error Loading Categories',
                'description' => 'There was a problem loading menu categories.'
            ]);
        }

        // Ensure categories is never null, even after exception handling
        if (!isset($this->categories) || !($this->categories instanceof \Illuminate\Support\Collection)) {
            $this->categories = collect();
        }
    }

    public function mount()
    {
        try {
            // Initialize products as empty collection
            $this->products = collect();

            // Don't reset selection if coming back to page
            if (!$this->selectedCategoryId) {
                $this->resetSelection();
            }
        } catch (\Exception $e) {
            Log::error('Error in PosIndex mount: ' . $e->getMessage());
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Error Loading Categories',
                'description' => 'There was a problem loading menu categories.'
            ]);
        }
    }

    protected function loadProductsForCategory(int $categoryId)
    {
        try {
            $this->isLoadingProducts = true;

            // Cache products by category to improve performance
            $cacheKey = 'products_category_' . $categoryId;

            $this->products = Cache::remember($cacheKey, $this->cacheExpiry, function () use ($categoryId) {
                return Product::select([
                    'id',
                    'name',
                    'description',
                    'price',
                    'category_id',
                    'image_path',
                    'has_variations'
                ])->where('category_id', $categoryId)
                    ->orderBy('name')
                    ->with(['variations' => function ($query) {
                        $query->select(['id', 'product_id', 'name', 'additional_price']);
                    }])
                    ->get();
            });

            Log::info('Products loaded for category ' . $categoryId . ': ' . $this->products->count());
        } catch (\Exception $e) {
            Log::error('Error loading products for category: ' . $e->getMessage());
        } finally {
            $this->isLoadingProducts = false;
        }
    }

    public function selectCategory(Category $category)
    {
        try {
            $this->selectedCategoryId = $category->id;
            $this->loadProductsForCategory($category->id);
        } catch (\Exception $e) {
            Log::error('Error in selectCategory: ' . $e->getMessage());
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Error Loading Products',
                'description' => 'There was a problem loading products for this category.'
            ]);
        }
    }

    public function selectProduct(Product $product)
    {
        // Only load full product details when selected
        if (isset($product->id)) {
            $cacheKey = 'product_details_' . $product->id;

            $this->selectedProduct = Cache::remember($cacheKey, $this->cacheExpiry, function () use ($product) {
                // Load the full product with all relations
                return Product::with([
                    'variations' => function ($query) {
                        $query->select(['id', 'product_id', 'name', 'additional_price'])
                            ->orderBy('name');
                    }
                ])->find($product->id);
            });

            $this->selectedVariationId = $this->selectedProduct->has_variations && $this->selectedProduct->variations->count() > 0
                ? $this->selectedProduct->variations->first()->id
                : null;

            $this->resetOptions();
        }
    }

    public function resetSelection()
    {
        $this->selectedProduct = null;
        $this->selectedVariationId = null;
        $this->resetOptions();
        $this->quantity = 1;
        $this->notes = null;
    }

    public function resetOptions()
    {
        $this->selectedOptions = [];
    }

    public function updateOption(string $name, string $type)
    {
        $this->selectedOptions[$name] = $type;
    }

    public function incrementQuantity()
    {
        $this->quantity++;
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart()
    {
        if (!$this->selectedProduct) {
            $this->dispatch('showToast', [
                'type' => 'warning',
                'message' => 'No Product Selected',
                'description' => 'Please select a product to add to cart.'
            ]);
            return;
        }

        try {
            $variation = null;
            $unitPrice = $this->selectedProduct->price;
            $productName = $this->selectedProduct->name;

            if ($this->selectedVariationId && $this->selectedProduct->has_variations) {
                $variation = $this->selectedProduct->variations->firstWhere('id', $this->selectedVariationId);
                $unitPrice += $variation ? $variation->additional_price : 0;
            }

            // Get option prices from cache when possible
            $optionPrice = 0;
            $optionsData = [];

            foreach ($this->selectedOptions as $name => $type) {
                $cacheKey = 'option_' . $name . '_' . $type;

                $option = Cache::remember($cacheKey, $this->cacheExpiry, function () use ($name, $type) {
                    return Option::where('name', $name)->where('type', $type)->first();
                });

                if ($option) {
                    $optionPrice += $option->additional_price;
                    $optionsData[] = [
                        'name' => $option->name,
                        'type' => $option->type,
                        'price' => $option->additional_price
                    ];
                }
            }

            $unitPrice += $optionPrice;
            $subtotal = $unitPrice * $this->quantity;

            $this->cart[] = [
                'product_id' => $this->selectedProduct->id,
                'product_name' => $productName,
                'variation_id' => $variation ? $variation->id : null,
                'variation_name' => $variation ? $variation->name : null,
                'options' => $optionsData,
                'quantity' => $this->quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'notes' => $this->notes
            ];

            $quantity = $this->quantity;

            $this->updateTotal();
            $this->resetSelection();

            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Item Added to Cart',
                'description' => 'Added ' . $quantity . 'x ' . $productName
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding to cart: ' . $e->getMessage());
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Error Adding to Cart',
                'description' => 'There was a problem adding this item to the cart.'
            ]);
        }
    }

    public function removeFromCart($index)
    {
        if (isset($this->cart[$index])) {
            $item = $this->cart[$index];
            $productName = $item['product_name'];

            unset($this->cart[$index]);
            $this->cart = array_values($this->cart); // Reindex array
            $this->updateTotal();

            $this->dispatch('showToast', [
                'type' => 'info',
                'message' => 'Item Removed',
                'description' => 'Removed ' . $productName . ' from cart'
            ]);
        }
    }

    public function updateTotal()
    {
        $this->total = 0;
        foreach ($this->cart as $item) {
            $this->total += $item['subtotal'];
        }
    }

    public function confirmOrderProcessing()
    {
        if (empty($this->cart)) {
            $this->dispatch('showToast', [
                'type' => 'warning',
                'message' => 'Empty Cart',
                'description' => 'Please add items to the cart before placing an order.'
            ]);
            return;
        }

        $this->dispatch('showConfirmation', [
            'title' => 'Process Order',
            'message' => 'Are you sure you want to process this order?',
            'confirmText' => 'Process Order',
            'cancelText' => 'Cancel',
            'action' => 'processOrderConfirmed'
        ]);
    }

    #[On('processOrderConfirmed')]
    public function processOrderConfirmed()
    {
        Log::info('processOrderConfirmed method called via attribute listener');
        $this->processOrder();
    }

    #[On('resetCartAfterOrder')]
    public function resetCartAfterOrder()
    {
        Log::info('Resetting cart after successful order');
        $this->cart = [];
        $this->total = 0;
        $this->customerName = null;
        $this->customerPhone = null;
        $this->deliveryAddress = null;
        $this->resetSelection();
    }

    public function processOrder()
    {
        Log::info('processOrder method started');

        if (empty($this->cart)) {
            $this->dispatch('showToast', [
                'type' => 'warning',
                'message' => 'Empty Cart',
                'description' => 'Please add items to the cart before placing an order.'
            ]);
            Log::info('Empty cart, returning early');
            return;
        }

        // Prevent double submission
        if ($this->isProcessingOrder) {
            return;
        }

        $this->isProcessingOrder = true;

        try {
            Log::info('Attempting to create order with data: ', [
                'total' => $this->total,
                'customer_name' => $this->customerName,
                'items_count' => count($this->cart)
            ]);

            // Using DB transaction for better performance and data integrity
            DB::beginTransaction();

            // Generate unique display_id manually before creating the order
            $displayId = $this->generateUniqueDisplayId();

            // Creating order with explicit array values to avoid any issues
            $orderData = [
                'user_id' => request()->user() ? request()->user()->id : 1, // Use authenticated user or default
                'total' => floatval($this->total),
                'status' => 'pending',
                'customer_name' => $this->customerName ?? '',
                'customer_phone' => $this->customerPhone ?? '',
                'delivery_address' => $this->deliveryAddress ?? '',
                'display_id' => $displayId
            ];

            Log::info('Order data prepared', $orderData);

            $order = new Order();
            $order->user_id = $orderData['user_id'];
            $order->total = $orderData['total'];
            $order->status = $orderData['status'];
            $order->customer_name = $orderData['customer_name'];
            $order->customer_phone = $orderData['customer_phone'];
            $order->delivery_address = $orderData['delivery_address'];
            $order->display_id = $orderData['display_id'];
            $order->save();

            $orderId = $order->id; // Store UUID in variable for use in log messages and toast
            Log::info('Order created with ID: ' . $orderId . ', display_id: ' . $displayId);

            // Instead of bulk insert, create each order item individually to ensure UUIDs are generated
            foreach ($this->cart as $item) {
                // Create each order item individually to ensure UUIDs are generated correctly
                $orderItem = new OrderItem();
                $orderItem->order_id = $orderId;
                $orderItem->product_id = $item['product_id'];
                $orderItem->variation_id = $item['variation_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->unit_price = floatval($item['unit_price']);
                $orderItem->subtotal = floatval($item['subtotal']);
                $orderItem->options = !empty($item['options']) ? $item['options'] : null;
                $orderItem->notes = $item['notes'] ?? '';
                $orderItem->save();
            }

            DB::commit();
            Log::info('Order items created successfully');

            // Clear any category and product caches after successful order
            $this->clearProductCaches();

            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Order Created',
                'description' => 'Order #' . $order->display_id . ' has been created successfully!'
            ]);

            // Refresh the order history component
            $this->dispatch('refreshOrderHistory');
            Log::info('Order process completed successfully');

            // Reset processing flag after success
            $this->isProcessingOrder = false;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in processOrder: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Order Creation Failed',
                'description' => 'An error occurred while processing the order: ' . $e->getMessage()
            ]);

            // Reset processing flag after failure
            $this->isProcessingOrder = false;
        }
    }

    // Generate a unique display ID (3-digit number)
    private function generateUniqueDisplayId()
    {
        $isUnique = false;
        $displayId = null;

        // Keep trying until we find a unique display_id
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

    // Helper method to clear product caches
    private function clearProductCaches()
    {
        if ($this->selectedCategoryId) {
            Cache::forget('products_category_' . $this->selectedCategoryId);
        }

        foreach ($this->cart as $item) {
            if (isset($item['product_id'])) {
                Cache::forget('product_details_' . $item['product_id']);
            }
        }
    }

    public function render()
    {
        return view('livewire.pos.pos-index');
    }
}
