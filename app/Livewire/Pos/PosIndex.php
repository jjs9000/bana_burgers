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

    public function mount()
    {
        try {
            $this->categories = Category::all();
            Log::info('Categories loaded: ' . $this->categories->count());
            $this->products = collect();
            $this->resetSelection();
        } catch (\Exception $e) {
            Log::error('Error in PosIndex mount: ' . $e->getMessage());
        }
    }

    public function selectCategory(Category $category)
    {
        try {
            $this->products = Product::where('category_id', $category->id)->get();
            Log::info('Products loaded for category ' . $category->id . ': ' . $this->products->count());
        } catch (\Exception $e) {
            Log::error('Error in selectCategory: ' . $e->getMessage());
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
            return;
        }

        $variation = null;
        $unitPrice = $this->selectedProduct->price;

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
            'product_name' => $this->selectedProduct->name,
            'variation_id' => $variation ? $variation->id : null,
            'variation_name' => $variation ? $variation->name : null,
            'options' => $optionsData,
            'quantity' => $this->quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'notes' => $this->notes
        ];

        $this->updateTotal();
        $this->resetSelection();
    }

    public function removeFromCart($index)
    {
        if (isset($this->cart[$index])) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart); // Reindex array
            $this->updateTotal();
        }
    }

    public function updateTotal()
    {
        $this->total = 0;
        foreach ($this->cart as $item) {
            $this->total += $item['subtotal'];
        }
    }

    public function processOrder()
    {
        if (empty($this->cart)) {
            return;
        }

        try {
            // Using hardcoded user ID 1 for the demo
            $order = Order::create([
                'user_id' => 1, // Use test user
                'total' => $this->total,
                'status' => 'pending'
            ]);

            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'options' => $item['options'],
                    'notes' => $item['notes']
                ]);
            }

            $this->cart = [];
            $this->total = 0;

            session()->flash('message', 'Order successfully created!');
        } catch (\Exception $e) {
            Log::error('Error in processOrder: ' . $e->getMessage());
            session()->flash('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pos.pos-index');
    }
}
