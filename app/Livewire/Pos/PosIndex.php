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

    // Customer Information
    public ?string $customerName = null;
    public ?string $customerPhone = null;
    public ?string $deliveryAddress = null;

    // Tab Navigation with state persistence
    #[Url]
    public string $activeTab = 'pos';

    #[Url]
    public ?int $selectedCategoryId = null;

    public function initComponent()
    {
        // This is called by wire:init in the template
        // Load products if category is already selected
        if ($this->selectedCategoryId) {
            $this->loadProductsForCategory($this->selectedCategoryId);
        }
    }

    public function mount()
    {
        try {
            $this->categories = Category::all();
            Log::info('Categories loaded: ' . $this->categories->count());
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
            $this->products = Product::where('category_id', $categoryId)->get();
            Log::info('Products loaded for category ' . $categoryId . ': ' . $this->products->count());
        } catch (\Exception $e) {
            Log::error('Error loading products for category: ' . $e->getMessage());
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
        $this->selectedProduct = $product;
        $this->selectedVariationId = $product->has_variations
            ? $product->variations->first()->id
            : null;
        $this->resetOptions();
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

        $variation = null;
        $unitPrice = $this->selectedProduct->price;
        $productName = $this->selectedProduct->name;

        if ($this->selectedVariationId) {
            $variation = $this->selectedProduct->variations->firstWhere('id', $this->selectedVariationId);
            $unitPrice += $variation ? $variation->additional_price : 0;
        }

        $optionPrice = 0;
        $optionsData = [];

        foreach ($this->selectedOptions as $name => $type) {
            $option = Option::where('name', $name)->where('type', $type)->first();
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

        try {
            Log::info('Attempting to create order with data: ', [
                'total' => $this->total,
                'customer_name' => $this->customerName,
                'items_count' => count($this->cart)
            ]);

            // Creating order with explicit array values to avoid any issues
            $orderData = [
                'user_id' => 1, // Use test user
                'total' => floatval($this->total),
                'status' => 'pending',
                'customer_name' => $this->customerName ?? '',
                'customer_phone' => $this->customerPhone ?? '',
                'delivery_address' => $this->deliveryAddress ?? ''
            ];

            Log::info('Order data prepared', $orderData);

            // Using hardcoded user ID 1 for the demo
            $order = new Order();
            $order->user_id = 1;
            $order->total = floatval($this->total);
            $order->status = 'pending';
            $order->customer_name = $this->customerName ?? '';
            $order->customer_phone = $this->customerPhone ?? '';
            $order->delivery_address = $this->deliveryAddress ?? '';
            $order->save();

            Log::info('Order created with ID: ' . $order->id);

            foreach ($this->cart as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $item['product_id'];
                $orderItem->variation_id = $item['variation_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->unit_price = floatval($item['unit_price']);
                $orderItem->subtotal = floatval($item['subtotal']);
                $orderItem->options = $item['options'];
                $orderItem->notes = $item['notes'] ?? '';
                $orderItem->save();
            }

            Log::info('Order items created successfully');

            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Order Created',
                'description' => 'Order #' . $order->id . ' has been created successfully!'
            ]);

            // Refresh the order history component
            $this->dispatch('refreshOrderHistory');
            Log::info('Order process completed successfully');
        } catch (\Exception $e) {
            Log::error('Error in processOrder: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Order Creation Failed',
                'description' => 'An error occurred while processing the order: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.pos.pos-index');
    }
}
