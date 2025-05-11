<div class="flex flex-col w-full bg-zinc-50 dark:bg-zinc-900">
    <!-- Order History Container -->
    <div class="w-full p-4">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold dark:text-white">Order History</h2>
                
                <div class="flex space-x-2">
                    <!-- Search Input -->
                    <div class="relative" x-data="{ showTooltip: false }">
                        <input 
                            wire:model.live.debounce.300ms="searchQuery" 
                            type="text" 
                            placeholder="Search by order #, customer, date, total, status, delivery..." 
                            class="pl-3 pr-10 py-2 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white text-sm w-80"
                        >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute right-3 top-2.5 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        
                        <!-- Search Help Tooltip -->
                        <div class="absolute -right-6 top-2.5">
                            <button 
                                type="button" 
                                @mouseenter="showTooltip = true" 
                                @mouseleave="showTooltip = false"
                                class="text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300 focus:outline-none"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                            <div 
                                x-show="showTooltip" 
                                x-transition:enter="transition ease-out duration-200" 
                                x-transition:enter-start="opacity-0 scale-95" 
                                x-transition:enter-end="opacity-100 scale-100" 
                                x-transition:leave="transition ease-in duration-100" 
                                x-transition:leave-start="opacity-100 scale-100" 
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute z-50 w-64 px-3 py-2 mt-2 -right-2 text-xs text-zinc-600 dark:text-zinc-300 bg-white dark:bg-zinc-700 rounded-md shadow-lg border border-zinc-200 dark:border-zinc-600"
                            >
                                You can search by:
                                <ul class="mt-1 list-disc list-inside">
                                    <li>Order number</li>
                                    <li>Customer name or phone</li>
                                    <li>Date (try "May 1" or "2023-05-01")</li>
                                    <li>Order total</li>
                                    <li>Order status ("pending", "completed")</li>
                                    <li>Delivery information</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <select 
                        wire:model.live="filterStatus" 
                        class="p-2 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white text-sm"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            
            @if(session()->has('message'))
                <div class="mb-4 p-2 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 rounded">
                    {{ session('message') }}
                </div>
            @endif
            
            <!-- Orders Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-700">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                Order #
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                Customer
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                Total
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                Delivery
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse($orders as $order)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium dark:text-white">
                                    #{{ $order->id }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $order->customer_name ?? $order->user->name ?? 'Guest' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $order->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium dark:text-white">
                                    RM{{ number_format($order->total, 2) }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                    ">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-300">
                                    @if($order->delivery_time)
                                        <span class="text-sm">
                                            {{ $order->delivery_time->format('M d, H:i') }}
                                            @if($order->delivery_status)
                                                <span class="ml-1 px-1.5 py-0.5 text-xs rounded-full 
                                                    {{ $order->delivery_status === 'delivered' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                                    {{ $order->delivery_status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                                    {{ $order->delivery_status === 'in_progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                                    {{ $order->delivery_status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                                ">
                                                    {{ str_replace('_', ' ', ucfirst($order->delivery_status)) }}
                                                </span>
                                            @endif
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button 
                                        wire:click="showOrderDetail({{ $order->id }})"
                                        class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white"
                                    >
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">
                                    No orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
    
    <!-- Order Details Modal -->
    @if($showOrderDetails && $selectedOrder)
    <div 
        x-data="{ 
            show: @entangle('showOrderDetails'),
            init() {
                $watch('show', value => {
                    if (!value) {
                        setTimeout(() => $wire.closeDetails(), 300);
                    }
                });
            }
        }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500/75 dark:bg-gray-900/80" @click="show = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div 
                x-show="show"
                class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-zinc-800 dark:text-white sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <div>
                    <div class="flex items-center justify-between pb-3">
                        <h3 class="text-xl font-bold dark:text-white">Order #{{ $selectedOrder->id }}</h3>
                        <button @click="show = false" class="flex items-center justify-center w-8 h-8 text-zinc-600 rounded-full hover:text-zinc-800 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:text-white dark:hover:bg-zinc-700">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>  
                        </button>
                    </div>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm mb-6">
                        Placed: {{ $selectedOrder->created_at->format('M d, Y H:i') }}
                    </p>
                    
                    <!-- Order Status Section -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-700 pb-3">
                            <h3 class="text-md font-semibold dark:text-white">Order Status</h3>
                            <span class="px-2 py-1 text-sm font-semibold rounded-full 
                                {{ $selectedOrder->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                {{ $selectedOrder->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                {{ $selectedOrder->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                            ">
                                {{ ucfirst($selectedOrder->status) }}
                            </span>
                        </div>
                        
                        <!-- Status Actions -->
                        <div class="flex space-x-2 mt-3">
                            <button 
                                wire:click="confirmCompleteOrder({{ $selectedOrder->id }})" 
                                @class([
                                    'px-3 py-2 rounded-md text-sm',
                                    'bg-green-600 text-white hover:bg-green-700' => $selectedOrder->status !== 'completed',
                                    'bg-green-200 text-green-700 cursor-not-allowed' => $selectedOrder->status === 'completed'
                                ])
                                @if($selectedOrder->status === 'completed') disabled @endif
                            >
                                Mark Completed
                            </button>
                            <button 
                                wire:click="confirmCancelOrder({{ $selectedOrder->id }})" 
                                @class([
                                    'px-3 py-2 rounded-md text-sm',
                                    'bg-red-600 text-white hover:bg-red-700' => $selectedOrder->status !== 'cancelled',
                                    'bg-red-200 text-red-700 cursor-not-allowed' => $selectedOrder->status === 'cancelled'
                                ])
                                @if($selectedOrder->status === 'cancelled') disabled @endif
                            >
                                Cancel Order
                            </button>
                        </div>
                    </div>
                    
                    <!-- Customer Details -->
                    <div class="mb-6">
                        <h3 class="text-md font-semibold dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-3 mb-3">Customer Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Name</p>
                                <p class="font-medium dark:text-white">{{ $selectedOrder->customer_name ?? $selectedOrder->user->name ?? 'Guest' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Phone</p>
                                <p class="font-medium dark:text-white">{{ $selectedOrder->customer_phone ?? 'Not provided' }}</p>
                            </div>
                            @if($selectedOrder->delivery_address)
                                <div class="md:col-span-2">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Delivery Address</p>
                                    <p class="font-medium dark:text-white">{{ $selectedOrder->delivery_address }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Delivery Information -->
                    <div class="mb-6">
                        <h3 class="text-md font-semibold dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-3 mb-3">Delivery Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-zinc-500 dark:text-zinc-400 mb-1">Delivery Time</label>
                                <input 
                                    type="datetime-local" 
                                    wire:model.live="deliveryTimes.{{ $selectedOrder->id }}" 
                                    class="w-full p-2 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white text-sm"
                                >
                            </div>
                            <div>
                                <label class="block text-sm text-zinc-500 dark:text-zinc-400 mb-1">Delivery Status</label>
                                <select 
                                    wire:model.live="deliveryStatuses.{{ $selectedOrder->id }}" 
                                    class="w-full p-2 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white text-sm"
                                >
                                    <option value="">Select Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <button 
                            wire:click="updateDeliveryInfo({{ $selectedOrder->id }})" 
                            class="mt-3 px-3 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-md text-sm"
                        >
                            Update Delivery Info
                        </button>
                    </div>
                    
                    <!-- Order Items -->
                    <div>
                        <h3 class="text-md font-semibold dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-3 mb-3">Order Items</h3>
                        <div class="space-y-4">
                            @foreach($selectedOrder->items as $item)
                                <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold dark:text-white">{{ $item->product->name }}</h4>
                                            @if($item->variation)
                                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $item->variation->name }}</p>
                                            @endif
                                            
                                            <!-- Options -->
                                            @if($item->options && count($item->options) > 0)
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                    @foreach($item->options as $option)
                                                        <span>{{ ucfirst($option['type']) }} {{ $option['name'] }}</span><br>
                                                    @endforeach
                                                </div>
                                            @endif
                                            
                                            <!-- Notes -->
                                            @if($item->notes)
                                                <p class="text-xs italic text-zinc-500 dark:text-zinc-400 mt-1">
                                                    Note: {{ $item->notes }}
                                                </p>
                                            @endif
                                            
                                            <div class="flex items-center mt-1">
                                                <span class="text-sm text-zinc-600 dark:text-zinc-300">
                                                    RM{{ number_format($item->unit_price, 2) }} x {{ $item->quantity }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <span class="font-medium dark:text-white">RM{{ number_format($item->subtotal, 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Order Total -->
                        <div class="mt-4 flex justify-between items-center border-t border-zinc-200 dark:border-zinc-700 pt-4">
                            <span class="font-bold dark:text-white">Total</span>
                            <span class="font-bold text-lg dark:text-white">RM{{ number_format($selectedOrder->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
