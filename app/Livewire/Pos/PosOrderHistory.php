<?php

namespace App\Livewire\Pos;

use App\Models\Order;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PosOrderHistory extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $selectedOrder = null;
    public $orderStatus = null;
    public $deliveryTimes = [];
    public $deliveryStatuses = [];
    public $isLoading = false;

    // Cache expiration time (in seconds)
    private $cacheExpiry = 300; // 5 minutes

    #[Url]
    public $searchQuery = '';

    #[Url]
    public $filterStatus = '';

    #[Url]
    public $page = 1;

    public $showOrderDetails = false;

    // Update when specific properties change
    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    // Set up listeners for live properties to update the table
    protected function getListeners()
    {
        $listeners = [];

        // Only add Echo listeners if broadcasting is enabled
        if (config('broadcasting.default') !== 'null' && class_exists('App\\Events\\OrderStatusUpdated')) {
            $listeners['echo:orders,OrderStatusUpdated'] = 'refreshHistory';
        }

        return $listeners;
    }

    public function mount()
    {
        $this->loadDeliveryInfo();
    }

    private function getUserIdentifier()
    {
        // Safer approach that doesn't rely on auth() helper directly
        return request()->user() ? request()->user()->id : 'guest';
    }

    public function loadDeliveryInfo()
    {
        // Only load delivery info for orders from the last 30 days to improve performance
        $cacheKey = 'delivery_info_' . $this->getUserIdentifier();

        $this->deliveryTimes = Cache::remember($cacheKey . '_times', $this->cacheExpiry, function () {
            $times = [];
            $orders = Order::where('created_at', '>=', now()->subDays(30))
                ->whereNotNull('delivery_time')
                ->select(['id', 'delivery_time'])
                ->get();

            foreach ($orders as $order) {
                if ($order->delivery_time) {
                    $times[$order->id] = $order->delivery_time->format('Y-m-d\TH:i');
                }
            }
            return $times;
        });

        $this->deliveryStatuses = Cache::remember($cacheKey . '_statuses', $this->cacheExpiry, function () {
            $statuses = [];
            $orders = Order::where('created_at', '>=', now()->subDays(30))
                ->whereNotNull('delivery_status')
                ->select(['id', 'delivery_status'])
                ->get();

            foreach ($orders as $order) {
                if ($order->delivery_status) {
                    $statuses[$order->id] = $order->delivery_status;
                }
            }
            return $statuses;
        });
    }

    public function resetFilters()
    {
        $this->searchQuery = '';
        $this->filterStatus = '';
        $this->selectedOrder = null;
        $this->showOrderDetails = false;
        $this->resetPage();
    }

    public function confirmCompleteOrder($orderId)
    {
        $this->dispatch('showConfirmation', [
            'title' => 'Complete Order',
            'message' => 'Are you sure you want to mark this order as completed?',
            'confirmText' => 'Complete Order',
            'cancelText' => 'Cancel',
            'action' => 'completeOrder',
            'params' => $orderId
        ]);
    }

    #[On('completeOrder')]
    public function completeOrder($orderId = null)
    {
        // For Livewire v3, the parameter comes in as an array, so we need to extract it
        if (is_array($orderId) && isset($orderId['orderId'])) {
            $orderId = $orderId['orderId'];
        }

        $order = Order::find($orderId);
        if (!$order) {
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Order not found',
                'description' => 'The order you are trying to complete cannot be found'
            ]);
            return;
        }

        // Use DB transaction for better performance and data integrity
        DB::beginTransaction();
        try {
            $order->status = 'completed';
            $order->save();
            DB::commit();

            // Clear relevant cache
            $this->clearOrderCache($orderId);

            // Refresh the selected order to update the UI
            if ($this->selectedOrder && $this->selectedOrder->id === $orderId) {
                $this->selectedOrder = $this->getOrderWithRelations($orderId);
            }

            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Order Completed',
                'description' => 'Order #' . $order->display_id . ' has been marked as completed'
            ]);

            // Dispatch a single refresh event
            $this->dispatch('refreshOrderHistory');
        } catch (\Exception $e) {
            DB::rollback();
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Error Completing Order',
                'description' => 'An error occurred while updating the order status'
            ]);
        }
    }

    public function confirmCancelOrder($orderId)
    {
        $this->dispatch('showConfirmation', [
            'title' => 'Cancel Order',
            'message' => 'Are you sure you want to cancel this order? This action cannot be undone.',
            'confirmText' => 'Cancel Order',
            'cancelText' => 'Keep Order',
            'action' => 'cancelOrder',
            'params' => $orderId
        ]);
    }

    public function confirmPrepareOrder($orderId)
    {
        $this->dispatch('showConfirmation', [
            'title' => 'Prepare Order',
            'message' => 'Are you ready to start preparing this order?',
            'confirmText' => 'Start Preparing',
            'cancelText' => 'Not Yet',
            'action' => 'prepareOrder',
            'params' => $orderId
        ]);
    }

    #[On('prepareOrder')]
    public function prepareOrder($orderId = null)
    {
        // For Livewire v3, the parameter comes in as an array, so we need to extract it
        if (is_array($orderId) && isset($orderId['orderId'])) {
            $orderId = $orderId['orderId'];
        }

        $order = Order::find($orderId);
        if (!$order) {
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Order not found',
                'description' => 'The order you are trying to update cannot be found'
            ]);
            return;
        }

        // Use DB transaction for better performance and data integrity
        DB::beginTransaction();
        try {
            $order->status = 'preparing';
            $order->save();
            DB::commit();

            // Clear relevant cache
            $this->clearOrderCache($orderId);

            // Refresh the selected order to update the UI
            if ($this->selectedOrder && $this->selectedOrder->id === $orderId) {
                $this->selectedOrder = $this->getOrderWithRelations($orderId);
            }

            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Order Being Prepared',
                'description' => 'Order #' . $order->display_id . ' is now being prepared'
            ]);

            // Dispatch a single refresh event
            $this->dispatch('refreshOrderHistory');
        } catch (\Exception $e) {
            DB::rollback();
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Error Updating Order',
                'description' => 'An error occurred while updating the order status'
            ]);
        }
    }

    public function updateDeliveryInfo($orderId)
    {
        $this->isLoading = true;

        $order = Order::find($orderId);
        if (!$order) {
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Order not found',
                'description' => 'The order you are trying to update cannot be found'
            ]);
            $this->isLoading = false;
            return;
        }

        DB::beginTransaction();
        try {
            if (isset($this->deliveryTimes[$orderId])) {
                $order->delivery_time = $this->deliveryTimes[$orderId];
            }

            if (isset($this->deliveryStatuses[$orderId])) {
                $order->delivery_status = $this->deliveryStatuses[$orderId];
            }

            $order->save();
            DB::commit();

            // Clear cache
            $this->clearOrderCache($orderId);

            // Update cached delivery info
            if (isset($this->deliveryTimes[$orderId])) {
                Cache::put('delivery_time_' . $orderId, $this->deliveryTimes[$orderId], $this->cacheExpiry);
            }

            if (isset($this->deliveryStatuses[$orderId])) {
                Cache::put('delivery_status_' . $orderId, $this->deliveryStatuses[$orderId], $this->cacheExpiry);
            }

            // Refresh the selected order to update the UI immediately
            if ($this->selectedOrder && $this->selectedOrder->id === $orderId) {
                $this->selectedOrder = $this->getOrderWithRelations($orderId);
            }

            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Delivery Information Updated',
                'description' => 'Delivery details for order #' . $order->display_id . ' have been updated'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Error Updating Delivery Info',
                'description' => 'An error occurred while updating delivery information'
            ]);
        }

        $this->isLoading = false;
    }

    public function showOrderDetail($orderId)
    {
        $this->isLoading = true;

        // Try to get from cache first
        $cacheKey = 'order_detail_' . $orderId;

        $order = Cache::remember($cacheKey, $this->cacheExpiry, function () use ($orderId) {
            return $this->getOrderWithRelations($orderId);
        });

        if ($order) {
            $this->selectedOrder = $order;
            $this->showOrderDetails = true;
        } else {
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Order not found',
                'description' => 'The requested order details cannot be found'
            ]);
        }

        $this->isLoading = false;
    }

    public function closeDetails()
    {
        $this->showOrderDetails = false;
        $this->selectedOrder = null;
    }

    /**
     * Navigate to the specific page in the pagination
     */
    public function gotoPage($page)
    {
        $this->page = (int) $page;
        $this->clearOrdersListCache(); // Clear cache when changing pages
    }

    #[On('refreshOrderHistory')]
    public function refreshHistory()
    {
        // Refresh the current page
        if ($this->selectedOrder) {
            $this->selectedOrder = $this->getOrderWithRelations($this->selectedOrder->id);
        }

        // Clear the cache for the orders list
        $this->clearOrdersListCache();
    }

    // Helper method to efficiently get order with relations
    private function getOrderWithRelations($orderId)
    {
        $order = Order::with([
            'items' => function ($query) {
                $query->select('id', 'order_id', 'product_id', 'variation_id', 'quantity', 'unit_price', 'subtotal', 'options', 'notes');
            },
            'items.product' => function ($query) {
                $query->select('id', 'name');
            },
            'items.variation' => function ($query) {
                $query->select('id', 'name');
            },
            'user' => function ($query) {
                $query->select('id', 'name');
            }
        ])->find($orderId);

        // Ensure options are properly decoded for each item
        if ($order && $order->items) {
            foreach ($order->items as $item) {
                // Make sure options is an array, even if null or invalid JSON
                if ($item->options === null || !is_array($item->options)) {
                    $item->options = [];
                }
            }
        }

        return $order;
    }

    // Helper method to clear cache
    private function clearOrderCache($orderId)
    {
        Cache::forget('order_detail_' . $orderId);
        $this->clearOrdersListCache();
    }

    private function clearOrdersListCache()
    {
        // Clear all possible pagination variants of the current query
        for ($i = 1; $i <= 10; $i++) {  // Clear at least 10 pages of cache
            $cacheKey = 'orders_list_' . $this->getUserIdentifier() . '_' . $this->searchQuery . '_' . $this->filterStatus . '_' . $i;
            Cache::forget($cacheKey);
        }

        // Also clear the delivery info caches
        $cacheKeys = [
            'delivery_info_' . $this->getUserIdentifier() . '_times',
            'delivery_info_' . $this->getUserIdentifier() . '_statuses'
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    #[On('cancelOrder')]
    public function cancelOrder($orderId = null)
    {
        // For Livewire v3, the parameter comes in as an array, so we need to extract it
        if (is_array($orderId) && isset($orderId['orderId'])) {
            $orderId = $orderId['orderId'];
        }

        $order = Order::find($orderId);
        if (!$order) {
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Order not found',
                'description' => 'The order you are trying to cancel cannot be found'
            ]);
            return;
        }

        // Use DB transaction for better performance and data integrity
        DB::beginTransaction();
        try {
            $order->status = 'cancelled';
            $order->save();
            DB::commit();

            // Clear relevant cache
            $this->clearOrderCache($orderId);

            // Refresh the selected order to update the UI
            if ($this->selectedOrder && $this->selectedOrder->id === $orderId) {
                $this->selectedOrder = $this->getOrderWithRelations($orderId);
            }

            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Order Cancelled',
                'description' => 'Order #' . $order->display_id . ' has been cancelled'
            ]);

            // Dispatch a single refresh event
            $this->dispatch('refreshOrderHistory');
        } catch (\Exception $e) {
            DB::rollback();
            \Illuminate\Support\Facades\Log::error('Error cancelling order: ' . $e->getMessage());
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Error Cancelling Order',
                'description' => 'An error occurred while updating the order status'
            ]);
        }
    }

    public function render()
    {
        // Generate cache key based on user, search query, filter, and pagination
        $currentPage = $this->page ?? 1;
        $cacheKey = 'orders_list_' . $this->getUserIdentifier() . '_' . $this->searchQuery . '_' . $this->filterStatus . '_' . $currentPage;

        try {
            // Before we load the data, set loading flag
            $this->isLoading = true;

            $orders = Cache::remember($cacheKey, $this->cacheExpiry, function () use ($currentPage) {
                $query = Order::with([
                    'items:id,order_id,product_id,variation_id',
                    'items.product:id,name',
                    'items.variation:id,name',
                    'user:id,name'
                ])->select([
                    'id',
                    'display_id',
                    'user_id',
                    'total',
                    'status',
                    'customer_name',
                    'customer_phone',
                    'delivery_time',
                    'delivery_status',
                    'created_at'
                ])->orderBy('created_at', 'desc');

                if ($this->searchQuery) {
                    $query->where(function ($q) {
                        // Search by Order ID
                        $q->where('id', 'like', '%' . $this->searchQuery . '%')
                            // Search by Display ID
                            ->orWhere('display_id', 'like', '%' . $this->searchQuery . '%')
                            // Search by Customer Name
                            ->orWhere('customer_name', 'like', '%' . $this->searchQuery . '%')
                            ->orWhereHas('user', function ($uq) {
                                $uq->where('name', 'like', '%' . $this->searchQuery . '%');
                            })
                            // Search by Customer Phone
                            ->orWhere('customer_phone', 'like', '%' . $this->searchQuery . '%')
                            // Search by Notes
                            ->orWhere('notes', 'like', '%' . $this->searchQuery . '%')
                            // Search by Status
                            ->orWhere('status', 'like', '%' . $this->searchQuery . '%')
                            // Search by Delivery Status
                            ->orWhere('delivery_status', 'like', '%' . $this->searchQuery . '%')
                            // Search by Delivery Address
                            ->orWhere('delivery_address', 'like', '%' . $this->searchQuery . '%');

                        // Search by Total - if the search query is numeric
                        if (is_numeric($this->searchQuery)) {
                            $q->orWhere('total', 'like', '%' . $this->searchQuery . '%');
                        }

                        // Search by Date
                        try {
                            $date = Carbon::parse($this->searchQuery);
                            $q->orWhereDate('created_at', $date)
                                ->orWhereDate('delivery_time', $date);
                        } catch (\Exception $e) {
                            // Not a valid date format, ignore date search
                        }
                    });
                }

                if ($this->filterStatus) {
                    $query->where('status', $this->filterStatus);
                }

                return $query->paginate(10, ['*'], 'page', $currentPage);
            });

            // After data is loaded, reset loading flag
            $this->isLoading = false;
        } catch (\Exception $e) {
            // Log the error but still show empty results
            \Illuminate\Support\Facades\Log::error('Error fetching orders: ' . $e->getMessage());
            $orders = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                10,
                $currentPage
            );
            $this->isLoading = false;
        }

        return view('livewire.pos.pos-order-history', [
            'orders' => $orders
        ]);
    }
}
