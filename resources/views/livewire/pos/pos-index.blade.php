<div class="flex flex-col lg:flex-row min-h-screen w-full bg-zinc-50 dark:bg-zinc-900">
    <!-- Main Container -->
    <div class="flex flex-col lg:flex-row w-full p-4 gap-4">
        <!-- Left Side - Menu Selection -->
        <div class="w-full lg:w-2/3 flex flex-col space-y-4">
            <!-- Categories -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4">
                <h2 class="text-lg font-bold mb-3 dark:text-white">Categories</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($categories as $category)
                        <button 
                            wire:click="selectCategory({{ $category->id }})"
                            class="px-4 py-2 rounded-md border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:text-white"
                        >
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>
            
            <!-- Products -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4 flex-grow">
                <h2 class="text-lg font-bold mb-3 dark:text-white">Menu Items</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($products as $product)
                        <div 
                            wire:click="selectProduct({{ $product->id }})"
                            class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg shadow cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-600 transition"
                        >
                            <h3 class="font-bold dark:text-white">{{ $product->name }}</h3>
                            <p class="text-zinc-500 dark:text-zinc-300 text-sm">{{ $product->description }}</p>
                            <p class="mt-2 font-semibold dark:text-white">RM{{ number_format($product->price, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Right Side - Order Details -->
        <div class="w-full lg:w-1/3 flex flex-col space-y-4">
            <!-- Product Customization -->
            @if($selectedProduct)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4">
                <h2 class="text-lg font-bold mb-3 dark:text-white">Customize {{ $selectedProduct->name }}</h2>
                
                <!-- Variations (if applicable) -->
                @if($selectedProduct->has_variations)
                <div class="mb-4">
                    <h3 class="font-medium mb-2 dark:text-white">Variation</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($selectedProduct->variations as $variation)
                            <button 
                                wire:click="$set('selectedVariationId', {{ $variation->id }})"
                                class="px-3 py-1 rounded-md text-sm border 
                                    {{ $selectedVariationId == $variation->id 
                                        ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' 
                                        : 'border-zinc-200 dark:border-zinc-700 dark:text-white' }}
                                    hover:bg-zinc-100 dark:hover:bg-zinc-700"
                            >
                                {{ $variation->name }} 
                                @if($variation->additional_price > 0)
                                    (+RM{{ number_format($variation->additional_price, 2) }})
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Options -->
                <div class="mb-4">
                    <h3 class="font-medium mb-2 dark:text-white">Options</h3>
                    
                    <!-- Onion Options -->
                    <div class="mb-2">
                        <p class="text-sm text-zinc-600 dark:text-zinc-300 mb-1">Onion</p>
                        <div class="flex flex-wrap gap-2">
                            <button 
                                wire:click="updateOption('Onion', 'extra')"
                                class="px-3 py-1 rounded-md text-sm border 
                                    {{ isset($selectedOptions['Onion']) && $selectedOptions['Onion'] === 'extra'
                                        ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' 
                                        : 'border-zinc-200 dark:border-zinc-700 dark:text-white' }}
                                    hover:bg-zinc-100 dark:hover:bg-zinc-700"
                            >
                                Extra
                            </button>
                            <button 
                                wire:click="updateOption('Onion', 'less')"
                                class="px-3 py-1 rounded-md text-sm border 
                                    {{ isset($selectedOptions['Onion']) && $selectedOptions['Onion'] === 'less'
                                        ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' 
                                        : 'border-zinc-200 dark:border-zinc-700 dark:text-white' }}
                                    hover:bg-zinc-100 dark:hover:bg-zinc-700"
                            >
                                Less
                            </button>
                            <button 
                                wire:click="updateOption('Onion', 'no')"
                                class="px-3 py-1 rounded-md text-sm border 
                                    {{ isset($selectedOptions['Onion']) && $selectedOptions['Onion'] === 'no'
                                        ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' 
                                        : 'border-zinc-200 dark:border-zinc-700 dark:text-white' }}
                                    hover:bg-zinc-100 dark:hover:bg-zinc-700"
                            >
                                No Onion
                            </button>
                        </div>
                    </div>
                    
                    <!-- Lettuce Options -->
                    <div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-300 mb-1">Lettuce</p>
                        <div class="flex flex-wrap gap-2">
                            <button 
                                wire:click="updateOption('Lettuce', 'extra')"
                                class="px-3 py-1 rounded-md text-sm border 
                                    {{ isset($selectedOptions['Lettuce']) && $selectedOptions['Lettuce'] === 'extra'
                                        ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' 
                                        : 'border-zinc-200 dark:border-zinc-700 dark:text-white' }}
                                    hover:bg-zinc-100 dark:hover:bg-zinc-700"
                            >
                                Extra
                            </button>
                            <button 
                                wire:click="updateOption('Lettuce', 'less')"
                                class="px-3 py-1 rounded-md text-sm border 
                                    {{ isset($selectedOptions['Lettuce']) && $selectedOptions['Lettuce'] === 'less'
                                        ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' 
                                        : 'border-zinc-200 dark:border-zinc-700 dark:text-white' }}
                                    hover:bg-zinc-100 dark:hover:bg-zinc-700"
                            >
                                Less
                            </button>
                            <button 
                                wire:click="updateOption('Lettuce', 'no')"
                                class="px-3 py-1 rounded-md text-sm border 
                                    {{ isset($selectedOptions['Lettuce']) && $selectedOptions['Lettuce'] === 'no'
                                        ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' 
                                        : 'border-zinc-200 dark:border-zinc-700 dark:text-white' }}
                                    hover:bg-zinc-100 dark:hover:bg-zinc-700"
                            >
                                No Lettuce
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Quantity -->
                <div class="mb-4">
                    <h3 class="font-medium mb-2 dark:text-white">Quantity</h3>
                    <div class="flex items-center">
                        <button 
                            wire:click="decrementQuantity"
                            class="flex items-center justify-center w-8 h-8 rounded-full border border-zinc-200 dark:border-zinc-700 dark:text-white"
                        >
                            -
                        </button>
                        <span class="mx-4 font-medium dark:text-white">{{ $quantity }}</span>
                        <button 
                            wire:click="incrementQuantity"
                            class="flex items-center justify-center w-8 h-8 rounded-full border border-zinc-200 dark:border-zinc-700 dark:text-white"
                        >
                            +
                        </button>
                    </div>
                </div>
                
                <!-- Notes -->
                <div class="mb-4">
                    <h3 class="font-medium mb-2 dark:text-white">Notes</h3>
                    <textarea 
                        wire:model.live="notes" 
                        class="w-full border border-zinc-200 dark:border-zinc-700 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white"
                        placeholder="Any special instructions..."
                        rows="2"
                    ></textarea>
                </div>
                
                <!-- Add to Cart -->
                <div class="flex justify-between">
                    <button 
                        wire:click="resetSelection"
                        class="px-4 py-2 border border-zinc-200 dark:border-zinc-700 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700 dark:text-white"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="addToCart"
                        class="px-4 py-2 bg-black text-white dark:bg-white dark:text-black rounded-md hover:bg-zinc-800 dark:hover:bg-zinc-200"
                    >
                        Add to Cart
                    </button>
                </div>
            </div>
            @endif
            
            <!-- Cart -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4 flex-grow">
                <h2 class="text-lg font-bold mb-3 dark:text-white">Order Summary</h2>
                
                @if(session()->has('message'))
                    <div class="mb-4 p-2 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 rounded">
                        {{ session('message') }}
                    </div>
                @endif
                
                @if(count($cart) > 0)
                    <div class="mb-4 space-y-3">
                        @foreach($cart as $index => $item)
                            <div class="border-b border-zinc-200 dark:border-zinc-700 pb-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold dark:text-white">{{ $item['product_name'] }}</h3>
                                        @if($item['variation_name'])
                                            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $item['variation_name'] }}</p>
                                        @endif
                                        
                                        <!-- Options -->
                                        @if(count($item['options']) > 0)
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                @foreach($item['options'] as $option)
                                                    <span>{{ ucfirst($option['type']) }} {{ $option['name'] }}</span><br>
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        <!-- Notes -->
                                        @if($item['notes'])
                                            <p class="text-xs italic text-zinc-500 dark:text-zinc-400 mt-1">
                                                Note: {{ $item['notes'] }}
                                            </p>
                                        @endif
                                        
                                        <div class="flex items-center mt-1">
                                            <span class="text-sm text-zinc-600 dark:text-zinc-300">
                                                RM{{ number_format($item['unit_price'], 2) }} x {{ $item['quantity'] }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium dark:text-white">RM{{ number_format($item['subtotal'], 2) }}</span>
                                        <button 
                                            wire:click="removeFromCart({{ $index }})"
                                            class="text-zinc-500 hover:text-red-500 dark:text-zinc-400 dark:hover:text-red-400"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-3">
                        <div class="flex justify-between items-center mb-4">
                            <span class="font-bold dark:text-white">Total</span>
                            <span class="font-bold text-lg dark:text-white">RM{{ number_format($total, 2) }}</span>
                        </div>
                        
                        <button 
                            wire:click="processOrder"
                            class="w-full py-3 bg-black text-white dark:bg-white dark:text-black rounded-md hover:bg-zinc-800 dark:hover:bg-zinc-200 font-medium"
                        >
                            Complete Order
                        </button>
                    </div>
                @else
                    <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                        <p>Your cart is empty</p>
                        <p class="text-sm mt-2">Select items from the menu to add them to your order.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
