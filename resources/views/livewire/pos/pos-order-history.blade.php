<div class="bg-zinc-50 dark:bg-zinc-900 w-full h-full">
    <!-- Preload Lottie animations with proper crossorigin attributes -->
    <link rel="preconnect" href="https://lottie.host" crossorigin>
    <link rel="dns-prefetch" href="https://lottie.host">
    
    <!-- Add a loading spinner overlay - but only for major operations, not filtering -->
    <div 
        wire:loading 
        wire:target="showOrderDetail, updateDeliveryInfo, confirmCompleteOrder, confirmCancelOrder, confirmPrepareOrder"
        class="fixed inset-0 bg-zinc-900/50 dark:bg-zinc-900/70 z-[200] flex items-center justify-center"
    >
        <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-white bg-zinc-800 dark:bg-zinc-700 transition ease-in-out duration-150">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Loading...
        </div>
    </div>

    <!-- Order History Container -->
    <div class="w-full h-full p-2 flex flex-col">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-3 flex flex-col flex-grow h-full">
            <div class="flex justify-between items-center mb-4 flex-shrink-0">
                <h2 class="text-lg font-bold dark:text-white">Order History</h2>
                
                <div class="flex space-x-2 items-center">
                    <!-- Search Help Tooltip -->
                    <div class="relative" x-data="{ showTooltip: false }">
                        <button 
                            type="button" 
                            @mouseenter="showTooltip = true" 
                            @mouseleave="showTooltip = false"
                            class="p-2 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300 focus:outline-none"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            class="absolute z-50 w-64 px-3 py-2 mt-2 right-0 text-xs text-zinc-600 dark:text-zinc-300 bg-white dark:bg-zinc-700 rounded-md shadow-lg border border-zinc-200 dark:border-zinc-600"
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

                    <!-- Search Input -->
                    <div class="relative">
                        <input 
                            wire:model.live.debounce.300ms="searchQuery" 
                            type="text" 
                            placeholder="Search order..." 
                            class="pl-3 pr-10 py-2 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white text-sm w-80"
                        >
                        <div class="absolute right-3 top-2.5 flex items-center">
                            <div wire:loading.delay wire:target="searchQuery" class="animate-spin mr-2">
                                <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <svg wire:loading.delay.remove wire:target="searchQuery" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="relative">
                        <select 
                            wire:model.live="filterStatus" 
                            class="pl-3 pr-10 py-2 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white text-sm min-w-[150px] appearance-none"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="preparing">Preparing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <div class="absolute right-3 top-2.5 flex items-center pointer-events-none">
                            <div wire:loading.delay wire:target="filterStatus" class="animate-spin">
                                <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <svg wire:loading.delay.remove wire:target="filterStatus" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            @if(session()->has('message'))
                <div class="mb-4 p-2 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 rounded flex-shrink-0">
                    {{ session('message') }}
                </div>
            @endif
            
            <!-- Orders Table -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg flex-grow flex flex-col relative h-full max-h-[calc(100vh-170px)]">
                <!-- Table-specific loading spinner that only shows when filtering or searching -->
                <div 
                    wire:loading.delay.shorter
                    wire:target="filterStatus, searchQuery, gotoPage"
                    class="absolute inset-0 bg-zinc-900/40 dark:bg-zinc-900/60 z-[100] flex items-center justify-center rounded-lg"
                >
                    <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-white bg-zinc-800 dark:bg-zinc-700 transition ease-in-out duration-150">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Updating results...
                    </div>
                </div>
                
                <!-- Fixed height table container with scroll -->
                <div class="overflow-auto flex-grow h-0 min-h-0 max-h-[calc(100vh-226px)]">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-700 sticky top-0 z-10">
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
                                <tr 
                                    wire:click="showOrderDetail('{{ $order->id }}')" 
                                    class="hover:bg-zinc-50 dark:hover:bg-zinc-700 cursor-pointer transition-colors duration-150"
                                >
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium dark:text-white">
                                        #{{ $order->display_id ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $order->customer_name ?? $order->user->name ?? 'Guest' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $order->created_at ? $order->created_at->format('M d, Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium dark:text-white">
                                        RM{{ number_format($order->total ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                            {{ $order->status === 'preparing' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : '' }}
                                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                        ">
                                            {{ ucfirst($order->status ?? 'unknown') }}
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
                                            wire:click.stop="showOrderDetail('{{ $order->id }}')"
                                            class="inline-flex items-center justify-center h-8 px-3 font-medium tracking-tight text-white bg-zinc-900 rounded-lg focus:outline-none hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 transition-colors duration-150"
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
                
                <!-- Pagination - Always at bottom with fixed height -->
                @if($orders->hasPages())
                <div class="flex items-center justify-between w-full h-16 px-3 border-t border-zinc-200 dark:border-zinc-700 flex-shrink-0">
                    <p class="pl-2 text-sm text-zinc-700 dark:text-zinc-300">
                        Showing <span class="font-medium">{{ $orders->firstItem() ?? 0 }}</span> to 
                        <span class="font-medium">{{ $orders->lastItem() ?? 0 }}</span> of 
                        <span class="font-medium">{{ $orders->total() }}</span> results
                    </p>
                    <nav>
                        <ul class="flex items-center text-sm leading-tight bg-white dark:bg-zinc-800 border divide-x rounded h-9 text-zinc-500 dark:text-zinc-400 divide-zinc-200 dark:divide-zinc-700 border-zinc-200 dark:border-zinc-700">
                            <li class="h-full">
                                <button type="button"
                                   wire:click="{{ $orders->onFirstPage() ? '' : 'gotoPage(' . ($orders->currentPage() - 1) . ')' }}"
                                   @class([
                                       'relative inline-flex items-center h-full px-3 ml-0 rounded-l group',
                                       'hover:text-zinc-900 dark:hover:text-white cursor-pointer' => !$orders->onFirstPage(),
                                       'opacity-50 cursor-not-allowed' => $orders->onFirstPage()
                                   ])
                                   {{ $orders->onFirstPage() ? 'disabled' : '' }}
                                >
                                    <span>Previous</span>
                                </button>
                            </li>
                            
                            @foreach ($orders->getUrlRange(max(1, $orders->currentPage() - 2), min($orders->lastPage(), $orders->currentPage() + 2)) as $page => $url)
                                <li class="hidden h-full md:block">
                                    <button type="button"
                                       wire:click="gotoPage({{ $page }})"
                                       @class([
                                           'relative inline-flex items-center h-full px-3 group',
                                           'text-zinc-900 dark:text-white bg-zinc-50 dark:bg-zinc-700' => $page == $orders->currentPage(),
                                           'hover:text-zinc-900 dark:hover:text-white' => $page != $orders->currentPage()
                                       ])
                                    >
                                        <span>{{ $page }}</span>
                                        <span class="box-content absolute bottom-0 {{ $page == $orders->currentPage() ? 'w-full' : 'w-0' }} h-px -mx-px translate-y-px 
                                            {{ $page == $orders->currentPage() ? 'border-l border-r border-zinc-900 dark:border-white bg-zinc-900 dark:bg-white' : 'border-transparent bg-zinc-900 dark:bg-white duration-200 ease-out group-hover:border-l group-hover:border-r group-hover:border-zinc-900 dark:group-hover:border-white left-1/2 group-hover:left-0 group-hover:w-full' }}">
                                        </span>
                                    </button>
                                </li>
                            @endforeach
                            
                            @if ($orders->lastPage() > 5 && $orders->currentPage() < $orders->lastPage() - 2)
                                <li class="hidden h-full md:block">
                                    <div class="relative inline-flex items-center h-full px-2.5 group">
                                        <span>...</span>
                                    </div>
                                </li>
                                <li class="hidden h-full md:block">
                                    <button type="button"
                                       wire:click="gotoPage({{ $orders->lastPage() }})"
                                       class="relative inline-flex items-center h-full px-3 group hover:text-zinc-900 dark:hover:text-white"
                                    >
                                        <span>{{ $orders->lastPage() }}</span>
                                        <span class="box-content absolute bottom-0 w-0 h-px -mx-px duration-200 ease-out translate-y-px border-transparent bg-zinc-900 dark:bg-white group-hover:border-l group-hover:border-r group-hover:border-zinc-900 dark:group-hover:border-white left-1/2 group-hover:left-0 group-hover:w-full"></span>
                                    </button>
                                </li>
                            @endif
                            
                            @if ($orders->lastPage() > 5 && $orders->currentPage() > 3)
                                <li class="hidden h-full md:block">
                                    <div class="relative inline-flex items-center h-full px-2.5 group">
                                        <span>...</span>
                                    </div>
                                </li>
                                <li class="hidden h-full md:block">
                                    <button type="button"
                                       wire:click="gotoPage(1)"
                                       class="relative inline-flex items-center h-full px-3 group hover:text-zinc-900 dark:hover:text-white"
                                    >
                                        <span>1</span>
                                        <span class="box-content absolute bottom-0 w-0 h-px -mx-px duration-200 ease-out translate-y-px border-transparent bg-zinc-900 dark:bg-white group-hover:border-l group-hover:border-r group-hover:border-zinc-900 dark:group-hover:border-white left-1/2 group-hover:left-0 group-hover:w-full"></span>
                                    </button>
                                </li>
                            @endif
                            
                            <li class="h-full">
                                <button type="button"
                                   wire:click="{{ $orders->hasMorePages() ? 'gotoPage(' . ($orders->currentPage() + 1) . ')' : '' }}"
                                   @class([
                                       'relative inline-flex items-center h-full px-3 rounded-r group',
                                       'hover:text-zinc-900 dark:hover:text-white cursor-pointer' => $orders->hasMorePages(),
                                       'opacity-50 cursor-not-allowed' => !$orders->hasMorePages()
                                   ])
                                   {{ !$orders->hasMorePages() ? 'disabled' : '' }}
                                >
                                    <span>Next</span>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
                @else
                <div class="w-full h-8 border-t border-zinc-200 dark:border-zinc-700 flex-shrink-0"></div>
                @endif
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
        class="fixed inset-0 z-[180]"
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
                style="max-height: 90vh;"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <div class="flex flex-col h-[calc(90vh-4rem)] overflow-hidden">
                    <div class="flex items-center justify-between pb-3 flex-shrink-0">
                        <h3 class="text-xl font-bold dark:text-white">Order #{{ $selectedOrder->display_id ?? 'N/A' }}</h3>
                        <button @click="show = false" class="flex items-center justify-center w-8 h-8 text-zinc-600 rounded-full hover:text-zinc-800 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:text-white dark:hover:bg-zinc-700">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>  
                        </button>
                    </div>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm mb-4 flex-shrink-0">
                        Placed: {{ $selectedOrder->created_at->format('M d, Y H:i') }}
                    </p>
                    
                    <!-- Order Status Placeholder Image -->
                    <div class="w-full mb-4 flex-shrink-0 text-center" id="order-status-container-{{ $selectedOrder->id }}">
                        <div class="w-full p-0 rounded-lg bg-zinc-100 dark:bg-zinc-700" id="order-status-image-{{ $selectedOrder->id }}">
                            @if($selectedOrder->status === 'completed')
                                <div class="w-full h-70 mx-auto">
                                    <iframe src="https://lottie.host/embed/9ecec3e3-274b-4dd8-84c2-c5ee22cf63b4/ZkmxKAgfES.lottie" 
                                        loading="eager" 
                                        importance="high" 
                                        style="width: 100%; height: 100%; border: none;" 
                                        allow="autoplay" 
                                        title="Order completed animation"></iframe>
                                </div>
                            @elseif($selectedOrder->status === 'pending')
                                <div class="w-full h-70 mx-auto">
                                    <iframe src="https://lottie.host/embed/c46328ef-9459-402b-bad2-ae8e4afc660c/E7qxLdv71x.lottie" 
                                        loading="eager" 
                                        importance="high" 
                                        style="width: 100%; height: 100%; border: none;" 
                                        allow="autoplay" 
                                        title="Order pending animation"></iframe>
                                </div>
                            @elseif($selectedOrder->status === 'preparing')
                                <div class="w-full h-70 mx-auto">
                                    <iframe src="https://lottie.host/embed/39653fe9-2a1d-4dd1-a2b2-90bac8c3a1d5/iL08MJfeZE.lottie" 
                                        loading="eager" 
                                        importance="high" 
                                        style="width: 100%; height: 100%; border: none;" 
                                        allow="autoplay" 
                                        title="Order preparing animation"></iframe>
                                </div>
                            @elseif($selectedOrder->status === 'cancelled')
                                <div class="w-full h-70 mx-auto">
                                    <iframe src="https://lottie.host/embed/7ed8c9b6-140f-4594-9c1d-f5fe8198a0f9/fEP0L7fo6o.lottie" 
                                        loading="eager" 
                                        importance="high" 
                                        style="width: 100%; height: 100%; border: none;" 
                                        allow="autoplay" 
                                        title="Order cancelled animation"></iframe>
                                </div>
                            @else
                                <div class="h-24 w-24 mx-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         class="w-full h-full text-blue-500" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Scrollable Content Area -->
                    <div class="overflow-y-auto flex-grow pr-2">
                        <!-- Order Status Section -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-700 pb-3">
                                <h3 class="text-md font-semibold dark:text-white">Order Status</h3>
                                <span class="px-2 py-1 text-sm font-semibold rounded-full 
                                    {{ $selectedOrder->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                    {{ $selectedOrder->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                    {{ $selectedOrder->status === 'preparing' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : '' }}
                                    {{ $selectedOrder->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                ">
                                    {{ ucfirst($selectedOrder->status) }}
                                </span>
                            </div>
                            
                            <!-- Status Actions -->
                            <div class="flex space-x-2 mt-3">
                                @if($selectedOrder->status === 'pending')
                                <button 
                                    wire:click="confirmPrepareOrder('{{ $selectedOrder->id }}')"
                                    class="px-3 py-2 rounded-md text-sm bg-yellow-600 text-white hover:bg-yellow-700"
                                >
                                    Start Preparing
                                </button>
                                @endif
                                <button 
                                    wire:click="confirmCompleteOrder('{{ $selectedOrder->id }}')" 
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
                                    wire:click="confirmCancelOrder('{{ $selectedOrder->id }}')" 
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
                        
                        <!-- Invoice Actions Section -->
                        <div class="mb-4">
                            <div class="border-b border-zinc-200 dark:border-zinc-700 pb-3 mb-3">
                                <h3 class="text-md font-semibold dark:text-white">Invoice</h3>
                            </div>
                            
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('invoice.generate', ['orderId' => $selectedOrder->id, 'output' => 'download']) }}" 
                                   class="inline-flex items-center px-3 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-md text-sm">
                                   <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    Download Invoice
                                </a>
                                
                                <button wire:click="$dispatch('openInvoiceModal', { orderId: '{{ $selectedOrder->id }}' })" 
                                   class="inline-flex items-center px-3 py-2 bg-zinc-600 text-white hover:bg-zinc-700 rounded-md text-sm">
                                   <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    View Invoice
                                </button>
                                
                                <button onclick="printInvoice('{{ $selectedOrder->id }}')"
                                   class="inline-flex items-center px-3 py-2 bg-zinc-500 text-white hover:bg-zinc-600 rounded-md text-sm">
                                   <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                                    </svg>
                                    Print Invoice
                                </button>
                            </div>
                        </div>
                        
                        <!-- Customer Details -->
                        <div class="mb-4">
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
                        <div class="mb-4">
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
                                wire:click="updateDeliveryInfo('{{ $selectedOrder->id }}')" 
                                class="mt-3 px-3 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-md text-sm"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="updateDeliveryInfo">Update Delivery Info</span>
                                <span wire:loading wire:target="updateDeliveryInfo" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Updating...
                                </span>
                            </button>
                        </div>
                        
                        <!-- Order Items -->
                        <div>
                            <h3 class="text-md font-semibold dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-3 mb-3">Order Items</h3>
                            <div class="space-y-4 pr-2">
                                @if($selectedOrder->items && count($selectedOrder->items) > 0)
                                    @foreach($selectedOrder->items as $item)
                                        <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h4 class="font-semibold dark:text-white">{{ $item->product ? $item->product->name : 'Unknown Product' }}</h4>
                                                    @if($item->variation)
                                                        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $item->variation->name }}</p>
                                                    @endif
                                                    
                                                    <!-- Options -->
                                                    @if($item->options && is_array($item->options) && count($item->options) > 0)
                                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                            @foreach($item->options as $option)
                                                                @if(is_array($option) && isset($option['type']) && isset($option['name']))
                                                                    <span>{{ ucfirst($option['type']) }} {{ $option['name'] }}</span><br>
                                                                @endif
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
                                @else
                                    <p class="text-center text-zinc-500 dark:text-zinc-400">No items found for this order.</p>
                                @endif
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
    </div>
    @endif
    <livewire:pos.view-invoice lazy />
</div>

<script>
    function printInvoice(orderId) {
        // Open the invoice in a new window
        var invoiceUrl = "{{ route('invoice.generate', ['orderId' => ':orderId', 'output' => 'stream']) }}";
        invoiceUrl = invoiceUrl.replace(':orderId', orderId);
        
        var printWindow = window.open(invoiceUrl, '_blank');
        
        // Wait for the page to load, then print
        printWindow.onload = function() {
            setTimeout(function() {
                printWindow.print();
            }, 1000); // Small delay to ensure PDF is fully loaded
        };
    }

    // Preload and cache all Lottie animations
    document.addEventListener('DOMContentLoaded', function() {
        // List of animation URLs (for reference only, we won't fetch directly)
        const animationUrls = [
            'https://lottie.host/embed/9ecec3e3-274b-4dd8-84c2-c5ee22cf63b4/ZkmxKAgfES.lottie',
            'https://lottie.host/embed/c46328ef-9459-402b-bad2-ae8e4afc660c/E7qxLdv71x.lottie',
            'https://lottie.host/embed/39653fe9-2a1d-4dd1-a2b2-90bac8c3a1d5/iL08MJfeZE.lottie',
            'https://lottie.host/embed/7ed8c9b6-140f-4594-9c1d-f5fe8198a0f9/fEP0L7fo6o.lottie'
        ];
        
        // Instead of using the Cache API which causes CORS issues,
        // we'll create hidden iframes to preload the animations in the browser cache
        function preloadWithIframes() {
            // Create a container to hold our preloading iframes
            const preloadContainer = document.createElement('div');
            preloadContainer.style.position = 'absolute';
            preloadContainer.style.width = '0';
            preloadContainer.style.height = '0';
            preloadContainer.style.opacity = '0';
            preloadContainer.style.pointerEvents = 'none';
            preloadContainer.style.overflow = 'hidden';
            document.body.appendChild(preloadContainer);
            
            // Create a hidden iframe for each animation
            animationUrls.forEach(url => {
                try {
                    const iframe = document.createElement('iframe');
                    iframe.src = url;
                    iframe.style.width = '1px';
                    iframe.style.height = '1px';
                    iframe.style.opacity = '0.01'; // Very slightly visible to ensure loading
                    iframe.style.position = 'absolute';
                    iframe.style.pointerEvents = 'none';
                    iframe.setAttribute('aria-hidden', 'true');
                    iframe.setAttribute('tabindex', '-1');
                    iframe.setAttribute('loading', 'lazy');
                    iframe.setAttribute('sandbox', 'allow-scripts allow-same-origin');
                    
                    // Add iframe to the container
                    preloadContainer.appendChild(iframe);
                    
                    // Remove iframe after it loads (or fails) to save resources
                    iframe.onload = iframe.onerror = function() {
                        setTimeout(() => {
                            preloadContainer.removeChild(iframe);
                            // If container is empty, remove it too
                            if (preloadContainer.children.length === 0) {
                                document.body.removeChild(preloadContainer);
                            }
                        }, 5000); // Keep loaded for a few seconds to ensure proper caching
                    };
                } catch (error) {
                    console.log('Preloading iframe failed:', error);
                }
            });
        }
        
        // Try the iframe preloading strategy
        preloadWithIframes();
        
        // Listen for order detail modal opening to ensure animations are ready
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('showOrderDetail', (orderId) => {
                // When a modal opens, we don't need to do anything special
                // The animations should be cached by the browser if preloading worked
            });
        });
    });
</script>
