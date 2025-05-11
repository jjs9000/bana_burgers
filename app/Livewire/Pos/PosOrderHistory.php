<?php

namespace App\Livewire\Pos;

use App\Models\Order;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Carbon\Carbon;

class PosOrderHistory extends Component
{
    use WithPagination;

    public $selectedOrder = null;
    public $orderStatus = null;
    public $deliveryTimes = [];
    public $deliveryStatuses = [];

    #[Url]
    public $searchQuery = '';

    #[Url]
    public $filterStatus = '';

    public $showOrderDetails = false;

    protected $listeners = ['refreshOrderHistory' => '$refresh'];

    public function mount()
    {
        $this->loadDeliveryInfo();
    }

    public function loadDeliveryInfo()
    {
        $orders = Order::all();
        foreach ($orders as $order) {
            if ($order->delivery_time) {
                $this->deliveryTimes[$order->id] = $order->delivery_time->format('Y-m-d\TH:i');
            }
            if ($order->delivery_status) {
                $this->deliveryStatuses[$order->id] = $order->delivery_status;
            }
        }
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

    public function completeOrder($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Order not found',
                'description' => 'The order you are trying to complete cannot be found'
            ]);
            return;
        }

        $order->status = 'completed';
        $order->save();

        $this->dispatch('showToast', [
            'type' => 'success',
            'message' => 'Order Completed',
            'description' => 'Order #' . $order->id . ' has been marked as completed'
        ]);

        $this->dispatch('refreshOrderHistory');
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

    public function cancelOrder($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Order not found',
                'description' => 'The order you are trying to cancel cannot be found'
            ]);
            return;
        }

        $order->status = 'cancelled';
        $order->save();

        $this->dispatch('showToast', [
            'type' => 'warning',
            'message' => 'Order Cancelled',
            'description' => 'Order #' . $order->id . ' has been cancelled'
        ]);

        $this->dispatch('refreshOrderHistory');
    }

    public function updateDeliveryInfo($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Order not found',
                'description' => 'The order you are trying to update cannot be found'
            ]);
            return;
        }

        if (isset($this->deliveryTimes[$orderId])) {
            $order->delivery_time = $this->deliveryTimes[$orderId];
        }

        if (isset($this->deliveryStatuses[$orderId])) {
            $order->delivery_status = $this->deliveryStatuses[$orderId];
        }

        $order->save();

        $this->dispatch('showToast', [
            'type' => 'success',
            'message' => 'Delivery Information Updated',
            'description' => 'Delivery details for order #' . $order->id . ' have been updated'
        ]);
    }

    public function showOrderDetail($orderId)
    {
        $order = Order::with(['items.product', 'items.variation', 'user'])->find($orderId);
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
    }

    public function closeDetails()
    {
        $this->showOrderDetails = false;
        $this->selectedOrder = null;
    }

    public function render()
    {
        $query = Order::with(['items.product', 'items.variation', 'user'])
            ->orderBy('created_at', 'desc');

        if ($this->searchQuery) {
            $query->where(function ($q) {
                // Search by Order ID
                $q->where('id', 'like', '%' . $this->searchQuery . '%')
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

        $orders = $query->paginate(10);

        return view('livewire.pos.pos-order-history', [
            'orders' => $orders
        ]);
    }
}
