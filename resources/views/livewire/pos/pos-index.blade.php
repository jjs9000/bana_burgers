<div class="bg-zinc-50 dark:bg-zinc-900 w-full h-full" wire:init="initComponent">
    <!-- Include Toast Component -->
    <x-toast />
    
    <!-- Include Confirmation Modal Component -->
    <x-confirmation-modal />
    
    <!-- Include GSAP library at top of page with correct attributes -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js" defer></script>

    <!-- Success Modal Popup -->
    <div id="orderSuccessModal" class="fixed inset-0 z-[160] flex items-center justify-center hidden" style="background-color: rgba(0, 0, 0, 0.5); pointer-events: none;">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg p-8 max-w-md w-full mx-4 transform transition-all">
            <div id="successTickAnimation" class="w-40 h-40 mx-auto mb-6"></div>
            <h2 
                id="successModalText" 
                class="invisible block pb-0.5 overflow-hidden text-2xl font-bold text-center text-green-600 dark:text-green-400 mb-4"
            >
                Order Successfully Created
            </h2>
            <p id="successOrderId" class="text-lg text-center text-zinc-600 dark:text-zinc-300"></p>
        </div>
    </div>

    <!-- Inline animation styles -->
    <style>
        .tick-animation-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
        }

        .tick-animation-svg {
            width: 100%;
            height: 100%;
            max-width: 300px;
            max-height: 300px;
        }

        .tick-circle {
            transform-origin: center;
            stroke-dasharray: 1000;
            stroke-dashoffset: 0;
            animation: tick-circle-animation 1s linear;
        }

        @keyframes tick-circle-animation {
            from {
                stroke-dashoffset: 1000;
                opacity: 0;
            }
            to {
                stroke-dashoffset: 0;
            }
        }

        .tick-mark {
            transform-origin: center;
            animation: tick-mark-animation 0.8s ease-in-out forwards;
            animation-delay: 0.2s; /* Start after circle begins */
            opacity: 0;
        }

        @keyframes tick-mark-animation {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            60% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>

    <!-- Inline animation script -->
    <script>
        // Create Tick Animation Function
        function createTickAnimation(container, options = {}) {
            if (!container) {
                console.error('No container element provided for tick animation');
                return;
            }

            // Default options
            const settings = {
                circleColor: options.circleColor || '#1C9943',
                tickColor: options.tickColor || '#1C9943',
                strokeWidth: options.strokeWidth || 10,
                onComplete: options.onComplete || function() {}
            };

            // Clear the container
            container.innerHTML = '';
            container.classList.add('tick-animation-container');

            // Create the SVG element
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('version', '1.1');
            svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            svg.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
            svg.setAttribute('xml:space', 'preserve');
            svg.classList.add('tick-animation-svg');
            svg.setAttribute('viewBox', '0 0 300 300');

            // Create the circle path
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            circle.classList.add('tick-circle');
            circle.setAttribute('stroke', settings.circleColor);
            circle.setAttribute('stroke-width', settings.strokeWidth);
            circle.setAttribute('fill', '#fff');
            circle.setAttribute('fill-opacity', '0');
            circle.setAttribute('stroke-miterlimit', '10');
            circle.setAttribute('d', 'M150,47.9c18.4,0,35.4,4.6,51,13.8s28,21.6,37.2,37.2s13.8,32.6,13.8,51s-4.6,35.4-13.8,51s-21.6,28-37.2,37.2s-32.6,13.8-51,13.8s-35.4-4.6-51-13.8s-28-21.6-37.2-37.2s-13.8-32.6-13.8-51s4.6-35.4,13.8-51s21.6-28,37.2-37.2S131.7,47.9,150,47.9z');

            // Create the tick mark path
            const tick = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            tick.classList.add('tick-mark');
            tick.setAttribute('fill', settings.tickColor);
            tick.setAttribute('stroke', '');
            tick.setAttribute('stroke-width', settings.strokeWidth);
            tick.setAttribute('d', 'M208.4,118.6c0.8-0.8,1.2-1.9,1.2-3.3c0-1.4-0.4-2.6-1.2-3.7l-3.7-3.3c-0.8-1.1-1.9-1.6-3.3-1.6s-2.6,0.4-3.7,1.2l-67,67l-28.4-28.8c-1.1-0.8-2.3-1.2-3.7-1.2c-1.4,0-2.5,0.4-3.3,1.2l-3.7,3.3c-0.8,1.1-1.2,2.3-1.2,3.7s0.4,2.5,1.2,3.3l35.4,35.8c1.1,1.1,2.3,1.6,3.7,1.6c1.4,0,2.5-0.5,3.3-1.6L208.4,118.6z');

            // Add elements to the SVG
            svg.appendChild(circle);
            svg.appendChild(tick);

            // Add SVG to the container
            container.appendChild(svg);

            // Set up the complete callback
            tick.addEventListener('animationend', settings.onComplete);

            return svg;
        }
    </script>

    <!-- Tab Navigation -->
    <div class="w-full bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 px-4 py-2 h-14 flex-shrink-0">
        <div
            x-data="{
                // Initialize tabSelected directly from Livewire state
                tabSelected: '{{ $activeTab === 'pos' ? '1' : '2' }}',
                tabId: $id('tabs'),
                tabButtonClicked(tabButton){
                    this.tabSelected = tabButton.id.replace(this.tabId + '-', '');
                    // Update Livewire property
                    if (this.tabSelected == 1) {
                        $wire.set('activeTab', 'pos');
                    } else if (this.tabSelected == 2) {
                        $wire.set('activeTab', 'history');
                    }
                    this.tabRepositionMarker(tabButton);
                },
                tabRepositionMarker(tabButton){
                    if (tabButton) {
                        this.$refs.tabMarker.style.width = tabButton.offsetWidth + 'px';
                        this.$refs.tabMarker.style.height = tabButton.offsetHeight + 'px';
                        this.$refs.tabMarker.style.left = tabButton.offsetLeft + 'px';
                    }
                }
            }"
            x-init="
                // Wait for DOM to be ready
                $nextTick(() => {
                    // Set the correct tab based on Livewire state
                    const activeTabIndex = '{{ $activeTab }}' === 'pos' ? 0 : 1;
                    const activeTabButton = $refs.tabButtons.children[activeTabIndex];
                    tabRepositionMarker(activeTabButton);
                });
                
                // Listen for Livewire updates
                $wire.$watch('activeTab', value => {
                    tabSelected = value === 'pos' ? '1' : '2';
                    $nextTick(() => {
                        const activeTabIndex = value === 'pos' ? 0 : 1;
                        const activeTabButton = $refs.tabButtons.children[activeTabIndex];
                        tabRepositionMarker(activeTabButton);
                    });
                });"
            class="relative w-full max-w-md"
        >
            <div x-ref="tabButtons" class="relative inline-grid items-center justify-center w-full h-10 grid-cols-2 p-1 text-zinc-500 dark:text-zinc-400 bg-white dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 rounded-lg select-none">
                <button 
                    :id="$id(tabId)" 
                    @click="tabButtonClicked($el);" 
                    type="button" 
                    :class="{ 'bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-white font-medium' : tabSelected === '1' }" 
                    class="relative z-20 inline-flex items-center justify-center w-full h-8 px-4 text-sm transition-all rounded-md cursor-pointer whitespace-nowrap"
                >
                    Point of Sale
                </button>
                <button 
                    :id="$id(tabId)" 
                    @click="tabButtonClicked($el);" 
                    type="button" 
                    :class="{ 'bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-white font-medium' : tabSelected === '2' }" 
                    class="relative z-20 inline-flex items-center justify-center w-full h-8 px-4 text-sm transition-all rounded-md cursor-pointer whitespace-nowrap"
                >
                    Order History
                </button>
                <div x-ref="tabMarker" class="absolute left-0 z-10 h-full duration-300 ease-out" x-cloak>
                    <div class="w-full h-full bg-zinc-100 dark:bg-zinc-700 rounded-md shadow-sm"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="w-full h-[calc(100%-3.5rem)]">
        <!-- POS Tab Content -->
        <div x-data="{}" x-show="$wire.activeTab === 'pos'" x-transition.opacity.duration.300ms class="h-full">
            <!-- Main Container -->
            <div class="flex flex-col lg:flex-row w-full h-full p-2 gap-2">
                <!-- Left Side - Menu Selection -->
                <div class="w-full lg:w-2/3 flex flex-col gap-2 h-full">
                    <!-- Categories -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-3 h-[80px] flex-shrink-0">
                        <h2 class="text-lg font-bold mb-2 dark:text-white">Categories</h2>
                        <div class="flex flex-wrap gap-2">
                            @if(isset($categories) && $categories instanceof \Illuminate\Support\Collection && $categories->count() > 0)
                                @foreach($categories as $category)
                                    <button 
                                        wire:click="selectCategory({{ $category->id }})"
                                        class="px-3 py-1 rounded-md border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:text-white text-sm"
                                    >
                                        {{ $category->name }}
                                    </button>
                                @endforeach
                            @else
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    No categories available
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Products -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-3 flex-grow flex flex-col">
                        <h2 class="text-lg font-bold mb-2 dark:text-white">Menu Items</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 overflow-y-auto flex-grow">
                            @foreach($products as $product)
                                <div 
                                    wire:click="selectProduct('{{ $product->id }}')"
                                    class="bg-zinc-50 dark:bg-zinc-700 p-3 rounded-lg shadow cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-600 transition h-[300px] flex flex-col"
                                >
                                    <!-- Standardized Product Image -->
                                    <div class="w-full h-[150px] mb-2 bg-zinc-200 dark:bg-zinc-600 rounded-md overflow-hidden flex-shrink-0">
                                        <img 
                                            src="{{ $product->image_path ? asset('storage/' . $product->image_path) : 'https://placehold.co/300x200/10B981/FFFFFF?text=' . urlencode($product->name) }}" 
                                            alt="{{ $product->name }}" 
                                            class="w-full h-full object-cover object-center"
                                            loading="lazy"
                                        >
                                    </div>
                                    <h3 class="font-bold dark:text-white text-sm line-clamp-1">{{ $product->name }}</h3>
                                    <p class="text-zinc-500 dark:text-zinc-300 text-xs line-clamp-2 flex-grow">{{ Str::limit($product->description, 40) }}</p>
                                    <p class="mt-1 font-semibold dark:text-white text-sm">RM{{ number_format($product->price, 2) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Order Details -->
                <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full">
                    <!-- Product Customization -->
                    @if($selectedProduct)
                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-3 flex-shrink-0">
                        <h2 class="text-lg font-bold mb-2 dark:text-white">Customize {{ $selectedProduct->name }}</h2>
                        
                        <!-- Variations (if applicable) -->
                        @if($selectedProduct->has_variations)
                        <div class="mb-3">
                            <h3 class="font-medium mb-1 dark:text-white text-sm">Variation</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($selectedProduct->variations as $variation)
                                    <button 
                                        wire:click="$set('selectedVariationId', {{ $variation->id }})"
                                        class="px-2 py-1 rounded-md text-xs border 
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
                        <div class="mb-3">
                            <h3 class="font-medium mb-1 dark:text-white text-sm">Options</h3>
                            
                            <!-- Onion Options -->
                            <div class="mb-2">
                                <p class="text-xs text-zinc-600 dark:text-zinc-300 mb-1">Onion</p>
                                <div class="flex flex-wrap gap-2">
                                    <button 
                                        wire:click="updateOption('Onion', 'extra')"
                                        class="px-2 py-1 rounded-md text-xs border 
                                            {{ isset($selectedOptions['Onion']) && $selectedOptions['Onion'] === 'extra'
                                                ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' 
                                                : 'border-zinc-200 dark:border-zinc-700 dark:text-white' }}
                                            hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                    >
                                        Extra
                                    </button>
                                    <button 
                                        wire:click="updateOption('Onion', 'less')"
                                        class="px-2 py-1 rounded-md text-xs border 
                                            {{ isset($selectedOptions['Onion']) && $selectedOptions['Onion'] === 'less'
                                                ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' 
                                                : 'border-zinc-200 dark:border-zinc-700 dark:text-white' }}
                                            hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                    >
                                        Less
                                    </button>
                                    <button 
                                        wire:click="updateOption('Onion', 'no')"
                                        class="px-2 py-1 rounded-md text-xs border 
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
                                <p class="text-xs text-zinc-600 dark:text-zinc-300 mb-1">Lettuce</p>
                                <div class="flex flex-wrap gap-2">
                                    <button 
                                        wire:click="updateOption('Lettuce', 'extra')"
                                        class="px-2 py-1 rounded-md text-xs border 
                                            {{ isset($selectedOptions['Lettuce']) && $selectedOptions['Lettuce'] === 'extra'
                                                ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' 
                                                : 'border-zinc-200 dark:border-zinc-700 dark:text-white' }}
                                            hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                    >
                                        Extra
                                    </button>
                                    <button 
                                        wire:click="updateOption('Lettuce', 'less')"
                                        class="px-2 py-1 rounded-md text-xs border 
                                            {{ isset($selectedOptions['Lettuce']) && $selectedOptions['Lettuce'] === 'less'
                                                ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' 
                                                : 'border-zinc-200 dark:border-zinc-700 dark:text-white' }}
                                            hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                    >
                                        Less
                                    </button>
                                    <button 
                                        wire:click="updateOption('Lettuce', 'no')"
                                        class="px-2 py-1 rounded-md text-xs border 
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
                        <div class="mb-3">
                            <h3 class="font-medium mb-1 dark:text-white text-sm">Quantity</h3>
                            <div class="flex items-center">
                                <button 
                                    wire:click="decrementQuantity"
                                    class="flex items-center justify-center w-6 h-6 rounded-full border border-zinc-200 dark:border-zinc-700 dark:text-white"
                                >
                                    -
                                </button>
                                <span class="mx-3 font-medium dark:text-white">{{ $quantity }}</span>
                                <button 
                                    wire:click="incrementQuantity"
                                    class="flex items-center justify-center w-6 h-6 rounded-full border border-zinc-200 dark:border-zinc-700 dark:text-white"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        <div class="mb-3">
                            <h3 class="font-medium mb-1 dark:text-white text-sm">Notes</h3>
                            <textarea 
                                wire:model.live="notes" 
                                class="w-full border border-zinc-200 dark:border-zinc-700 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white text-xs"
                                placeholder="Any special instructions..."
                                rows="2"
                            ></textarea>
                        </div>
                        
                        <!-- Add to Cart -->
                        <div class="flex justify-between">
                            <button 
                                wire:click="resetSelection"
                                class="px-3 py-1 border border-zinc-200 dark:border-zinc-700 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700 dark:text-white text-sm"
                            >
                                Cancel
                            </button>
                            <button 
                                wire:click="addToCart"
                                class="px-3 py-1 bg-black text-white dark:bg-white dark:text-black rounded-md hover:bg-zinc-800 dark:hover:bg-zinc-200 text-sm"
                            >
                                Add to Cart
                            </button>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Cart -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-3 flex flex-col {{ count($cart) === 0 ? ($selectedProduct ? 'h-[150px]' : 'h-[200px]') : 'flex-grow max-h-[calc(100vh-380px)]' }}">
                        <h2 class="text-lg font-bold mb-2 dark:text-white flex-shrink-0">Order Summary</h2>
                        
                        <!-- Regular Cart View -->
                        <div id="cartView" class="flex flex-col flex-grow">
                            @if(count($cart) > 0)
                                <div class="mb-3 space-y-2 overflow-y-auto flex-grow max-h-[calc(100vh-480px)]">
                                    @foreach($cart as $index => $item)
                                        <div class="border-b border-zinc-200 dark:border-zinc-700 pb-2">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h3 class="font-semibold dark:text-white text-sm">{{ $item['product_name'] }}</h3>
                                                    @if($item['variation_name'])
                                                        <p class="text-xs text-zinc-600 dark:text-zinc-300">{{ $item['variation_name'] }}</p>
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
                                                        <span class="text-xs text-zinc-600 dark:text-zinc-300">
                                                            RM{{ number_format($item['unit_price'], 2) }} x {{ $item['quantity'] }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium dark:text-white text-xs">RM{{ number_format($item['subtotal'], 2) }}</span>
                                                    <button 
                                                        wire:click="removeFromCart({{ $index }})"
                                                        class="text-zinc-500 hover:text-red-500 dark:text-zinc-400 dark:hover:text-red-400"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="border-t border-zinc-200 dark:border-zinc-700 pt-2 flex-shrink-0">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="font-bold dark:text-white">Total</span>
                                        <span class="font-bold text-lg dark:text-white">RM{{ number_format($total, 2) }}</span>
                                    </div>

                                    <!-- Customer Information -->
                                    <div class="mb-3">
                                        <h3 class="font-medium mb-1 dark:text-white text-sm">Customer Information</h3>
                                        <div class="space-y-2">
                                            <div>
                                                <label class="block text-xs text-zinc-500 dark:text-zinc-400 mb-1">Customer Name</label>
                                                <input 
                                                    type="text"
                                                    wire:model="customerName" 
                                                    class="w-full p-1 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white text-xs"
                                                    placeholder="Enter customer name"
                                                >
                                            </div>
                                            <div>
                                                <label class="block text-xs text-zinc-500 dark:text-zinc-400 mb-1">Phone Number</label>
                                                <input 
                                                    type="text"
                                                    wire:model="customerPhone" 
                                                    class="w-full p-1 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white text-xs"
                                                    placeholder="Enter phone number"
                                                >
                                            </div>
                                            <div>
                                                <label class="block text-xs text-zinc-500 dark:text-zinc-400 mb-1">Delivery Address</label>
                                                <textarea 
                                                    wire:model="deliveryAddress" 
                                                    class="w-full p-1 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white text-xs"
                                                    placeholder="Enter delivery address (if applicable)"
                                                    rows="2"
                                                ></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button 
                                        wire:click="confirmOrderProcessing"
                                        class="w-full py-2 bg-black text-white dark:bg-white dark:text-black rounded-md hover:bg-zinc-800 dark:hover:bg-zinc-200 font-medium text-sm"
                                    >
                                        Complete Order
                                    </button>
                                </div>
                            @else
                                <div class="text-center py-4 text-zinc-500 dark:text-zinc-400">
                                    <p>Your cart is empty</p>
                                    <p class="text-xs mt-1">Select items from the menu to add them to your order.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order History Tab -->
        <div x-data="{}" x-show="$wire.activeTab === 'history'" x-transition.opacity.duration.300ms class="h-full">
            @livewire('pos.pos-order-history')
        </div>
    </div>
    
    <!-- JavaScript for Livewire-Alpine integration -->
    <script>
        // Keep track of whether the listener has been registered to prevent duplicates
        window.toastListenerRegistered = window.toastListenerRegistered || false;
        
        document.addEventListener('livewire:init', function() {
            // Prevent duplicate registration of toast event listeners
            if (window.toastListenerRegistered) {
                console.log('Toast listener already registered, skipping duplicate registration');
                return;
            }
            
            // Mark as registered to prevent duplicates
            window.toastListenerRegistered = true;
            
            // In Livewire 3, we need to use Livewire.removeEventListener instead of off
            // Try to clean up existing listeners if any
            try {
                Livewire.removeEventListener('showToast');
            } catch (e) {
                console.log('No previous toast listeners to clean up');
            }
            
            // Handle toast notifications
            Livewire.on('showToast', (data) => {
                console.log('Toast notification triggered:', data);
                window.toast(data[0].message, {
                    type: data[0].type,
                    description: data[0].description
                });
                
                // If this is a success message for order creation, show the animation
                if (data[0].type === 'success' && data[0].message.includes('Order Created')) {
                    // Extract order ID from the message - updated to match 3-digit format
                    const orderIdMatch = data[0].description.match(/Order #(\d{3})/);
                    const orderId = orderIdMatch ? orderIdMatch[1] : '';
                    
                    console.log('Success toast detected for order creation - Order ID:', orderId);
                    
                    // Prevent any other actions during animation duration
                    // Show the success animation with a small delay to ensure toast is processed first
                    setTimeout(() => {
                        showOrderSuccessAnimation(orderId);
                    }, 100);
                    
                    // Return early to prevent any other processing during animation
                    return;
                }
            });
            
            // Clean up event listener when the component is disconnected
            document.addEventListener('livewire:disconnect', function() {
                if (window.toastListenerRegistered) {
                    try {
                        Livewire.removeEventListener('showToast');
                    } catch (e) {
                        console.log('Error cleaning up toast listener:', e);
                    }
                    window.toastListenerRegistered = false;
                    console.log('Toast listener removed during component disconnect');
                }
                
                if (window.confirmationListenerRegistered) {
                    try {
                        Livewire.removeEventListener('showConfirmation');
                    } catch (e) {
                        console.log('Error cleaning up confirmation listener:', e);
                    }
                    window.confirmationListenerRegistered = false;
                    console.log('Confirmation listener removed during component disconnect');
                }
            });
            
            // Keep track of confirmation listener
            window.confirmationListenerRegistered = window.confirmationListenerRegistered || false;
            
            // Clean up previous confirmation listeners
            if (window.confirmationListenerRegistered) {
                try {
                    Livewire.removeEventListener('showConfirmation');
                } catch (e) {
                    console.log('No previous confirmation listeners to clean up');
                }
            }
            window.confirmationListenerRegistered = true;
            
            // Handle confirmation modal
            Livewire.on('showConfirmation', (data) => {
                console.log('Confirmation modal triggered:', data);
                window.confirmAction({
                    title: data[0].title,
                    message: data[0].message,
                    confirmText: data[0].confirmText,
                    cancelText: data[0].cancelText,
                    onConfirm: () => {
                        console.log('Confirmation confirmed, dispatching action:', data[0].action);
                        // Check if we have an orderId parameter
                        if (data[0].params) {
                            console.log('With params:', data[0].params);
                            // For debugging, dump the params
                            console.log('Params type:', typeof data[0].params, 'Value:', data[0].params);
                            
                            // Create a proper payload
                            const payload = { orderId: data[0].params };
                            console.log('Dispatching with payload:', payload);
                            
                            // Dispatch with payload
                            Livewire.dispatch(data[0].action, payload);
                        } else {
                            console.log('Without params, dispatching to:', data[0].action);
                            try {
                                Livewire.dispatch(data[0].action);
                                console.log('Dispatch successful');
                            } catch (error) {
                                console.error('Error dispatching Livewire event:', error);
                            }
                        }
                    }
                });
            });
        });
        
        // Load necessary scripts immediately instead of waiting for DOMContentLoaded
        // Our animations are loaded via asset() at the top of the page
        
        // Function to show the success animation
        function showOrderSuccessAnimation(orderId) {
            console.log('Showing order success animation for order:', orderId);
            
            // Block page interaction during animation
            document.body.style.pointerEvents = 'none';
            
            // Force load our custom tick animation script if not already loaded
            if (typeof createTickAnimation !== 'function') {
                console.error('Animation function not found, attempting to load it');
                const script = document.createElement('script');
                script.src = '{{ asset('animations/tick-animation.js') }}';
                script.onload = function() {
                    console.log('Animation script loaded successfully, retrying animation');
                    // Retry after script loads
                    showOrderSuccessAnimation(orderId);
                };
                document.head.appendChild(script);
                document.body.style.pointerEvents = 'auto'; // Re-enable interaction if script loading fails
                return; // Stop and wait for script to load
            }
            
            // Show the success modal
            const successModal = document.getElementById('orderSuccessModal');
            if (!successModal) {
                console.error('Success modal not found in the DOM');
                alert('Order #' + orderId + ' created successfully!'); // Fallback for debugging
                return;
            }
            
            // Update the order ID text
            const orderIdEl = document.getElementById('successOrderId');
            if (orderIdEl) {
                orderIdEl.textContent = `Order #${orderId} has been created successfully!`;
            } else {
                console.error('Order ID element not found in the modal');
            }
            
            // Initialize the tick animation
            initSuccessTickAnimation();
            
            // Animate the success text
            animateSuccessText();
            
            // Show the modal with a fade-in effect
            console.log('Showing success modal');
            successModal.classList.remove('hidden');
            successModal.style.opacity = '0';
            setTimeout(() => {
                successModal.style.transition = 'opacity 0.3s ease-out';
                successModal.style.opacity = '1';
                
                // Auto-close after 2 seconds
                setTimeout(() => {
                    console.log('Animation display time complete, closing modal');
                    closeSuccessModal();
                }, 2000);
            }, 10);
        }
        
        // Function to close the success modal
        function closeSuccessModal() {
            const successModal = document.getElementById('orderSuccessModal');
            if (!successModal) return;
            
            // Fade out effect
            successModal.style.transition = 'opacity 0.3s ease-in';
            successModal.style.opacity = '0';
            
            // Hide after animation completes and reset cart
            setTimeout(() => {
                successModal.classList.add('hidden');
                resetCart(); // Move resetCart here to ensure it runs after modal is fully closed
                document.body.style.pointerEvents = 'auto'; // Re-enable interaction after animation ends
            }, 300);
        }
        
        // Reset the cart after successful order
        function resetCart() {
            // Simply reload the POS view for now
            // In a more advanced implementation, we could just reset the cart data without a reload
            if (window.Livewire) {
                // Reset the cart without reloading
                // Use dispatch instead of emit for Livewire v3
                Livewire.dispatch('resetCartAfterOrder');
                console.log('Dispatched resetCartAfterOrder event');
            }
        }
        
        // Function to animate the success text using GSAP (Pines UI style)
        function animateSuccessText() {
            const successTextEl = document.getElementById('successModalText');
            if (!successTextEl) {
                console.error('Success text element not found');
                return;
            }
            
            // Check if GSAP is loaded
            if (typeof gsap === 'undefined') {
                console.log('GSAP not loaded yet, waiting...');
                const gsapCheckInterval = setInterval(() => {
                    if (typeof gsap !== 'undefined') {
                        console.log('GSAP loaded, animating text');
                        clearInterval(gsapCheckInterval);
                        performTextAnimation(successTextEl);
                    }
                }, 50);
                return;
            }
            
            performTextAnimation(successTextEl);
        }
        
        // Helper function to perform the Pines UI text animation
        function performTextAnimation(element) {
            if (!element) {
                console.error('Animation element is undefined');
                return;
            }
            
            // Split text into individual spans
            const text = element.textContent.trim();
            let html = '';
            for (let i = 0; i < text.length; i++) {
                const classAttr = text[i].trim() ? 'class="inline-block"' : '';
                html += `<span ${classAttr}>${text[i]}</span>`;
            }
            element.innerHTML = html;
            
            // Make element visible but keep children invisible
            element.classList.remove('invisible');
            
            // Ensure we have children elements
            if (!element.children || element.children.length === 0) {
                console.error('No children elements to animate');
                return;
            }
            
            // Animate with GSAP
            const startingAnimation = { opacity: 0, y: 50, rotation: '25deg' };
            const endingAnimation = { 
                opacity: 1, 
                y: 0, 
                rotation: '0deg', 
                stagger: 0.02, 
                duration: 0.7, 
                ease: 'back' 
            };
            
            gsap.fromTo(element.children, startingAnimation, endingAnimation);
        }
        
        // Initialize the tick animation using our custom SVG animation
        function initSuccessTickAnimation() {
            console.log('Initializing success tick animation');
            
            const tickContainer = document.getElementById('successTickAnimation');
            if (!tickContainer) {
                console.error('Tick animation container not found');
                return;
            }
            
            // Use our custom SVG tick animation
            if (typeof createTickAnimation === 'function') {
                console.log('Creating SVG tick animation');
                createTickAnimation(tickContainer, {
                    circleColor: '#10B981', // Tailwind green-500
                    tickColor: '#10B981',
                    onComplete: function() {
                        console.log('Tick animation completed');
                    }
                });
            } else {
                console.error('Custom tick animation function not found');
                // Fallback text in case animation fails
                tickContainer.innerHTML = '<div class="flex items-center justify-center w-full h-full"><svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>';
            }
        }
    </script>

    <!-- Add a script to ensure proper tab state on page refresh -->
    <script>
        document.addEventListener('livewire:navigated', () => {
            // Force a recalculation of tab state after navigation/refresh
            const activeTab = @js($activeTab);
            
            // Give Alpine time to initialize components first
            setTimeout(() => {
                if (activeTab === 'pos') {
                    document.querySelector('[x-show="$wire.activeTab === \'pos\'"]').style.display = 'block';
                    document.querySelector('[x-show="$wire.activeTab === \'history\'"]').style.display = 'none';
                } else {
                    document.querySelector('[x-show="$wire.activeTab === \'pos\'"]').style.display = 'none';
                    document.querySelector('[x-show="$wire.activeTab === \'history\'"]').style.display = 'block';
                }
            }, 10);
        });
    </script>
</div>
