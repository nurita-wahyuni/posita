<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\BoxOrder;
use App\Models\BoxTemplate;
use App\Services\AdminDataService;
use App\Services\BoxOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoxOrderController extends Controller
{
    public function __construct(
        protected BoxOrderService $boxOrderService,
        protected AdminDataService $adminDataService
    ) {
    }

    /**
     * Display box order listing with countdown timers.
     */
    public function index(): Response
    {
        $upcomingOrders = $this->boxOrderService->getUpcomingOrders();
        $todayOrders = $this->boxOrderService->getTodayOrders();

        // Add time remaining to each order
        $upcomingOrders = $upcomingOrders->map(function ($order) {
            $order->time_remaining = $order->getTimeRemainingAttribute();
            return $order;
        });

        return Inertia::render('Pos/Box/Index', [
            'upcomingOrders' => $upcomingOrders,
            'todayOrders' => $todayOrders,
        ]);
    }

    /**
     * Show form to create a new order (flexible line items).
     */
    public function create(BoxTemplate $template = null): Response
    {
        return Inertia::render('Pos/Box/Create', [
            'selectedTemplate' => $template,
        ]);
    }

    /**
     * Store a new box order with flexible line items.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'pickup_datetime' => 'required|date|after:now',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $order = $this->boxOrderService->createOrder($validated);

            return redirect()
                ->route('pos.box.index')
                ->with('success', 'Order berhasil dibuat untuk ' . $order->customer_name);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Upload payment proof for an order.
     */
    public function uploadProof(Request $request, int $orderId): RedirectResponse
    {
        $validated = $request->validate([
            'payment_proof' => 'required|image|max:5120', // 5MB max
        ]);

        $order = BoxOrder::findOrFail($orderId);

        try {
            $this->boxOrderService->uploadPaymentProof($order, $request->file('payment_proof'));

            return redirect()
                ->back()
                ->with('success', 'Bukti pembayaran berhasil diupload.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, int $orderId): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,completed,cancelled',
        ]);

        $order = BoxOrder::findOrFail($orderId);

        try {
            $this->boxOrderService->updateOrderStatus($order, $validated['status']);

            return redirect()
                ->back()
                ->with('success', 'Status order berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Download receipt PDF for an order.
     */
    public function downloadReceipt(BoxOrder $order)
    {
        return $this->boxOrderService->generateReceipt($order);
    }
}
