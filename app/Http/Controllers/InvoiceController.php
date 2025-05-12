<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * Generate a PDF invoice for an order
     * 
     * @param string $orderId
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateInvoice($orderId, Request $request)
    {
        try {
            $order = Order::with(['items.product', 'items.variation', 'user'])->findOrFail($orderId);

            $pdf = PDF::loadView('pdf.invoice', ['order' => $order]);

            // Determine output type
            $output = $request->get('output', 'download');

            if ($output === 'stream') {
                return $pdf->stream("invoice-{$order->display_id}.pdf");
            } else if ($output === 'download') {
                return $pdf->download("invoice-{$order->display_id}.pdf");
            }

            // Default is to download
            return $pdf->download("invoice-{$order->display_id}.pdf");
        } catch (\Exception $e) {
            Log::error('Error generating invoice: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to generate invoice',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
