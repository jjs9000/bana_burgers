<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class ViewInvoice extends Component
{
    public $orderId = null;
    public $showModal = false;

    #[On('openInvoiceModal')]
    public function openModal($orderId = null)
    {
        // Handle both string and array parameters
        if (is_array($orderId)) {
            $this->orderId = $orderId['orderId'] ?? null;
        } else {
            $this->orderId = $orderId;
        }

        // Make sure we have a valid orderId before showing the modal
        if (empty($this->orderId)) {
            // Log the error but don't show modal
            Log::error('ViewInvoice: Attempted to open modal with empty orderId');
            return;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->orderId = null;
    }

    public function render()
    {
        return view('livewire.pos.view-invoice');
    }
}
