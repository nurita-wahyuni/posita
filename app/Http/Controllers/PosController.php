<?php

namespace App\Http\Controllers;

use App\Models\DailyConsignment;
use App\Models\ShopSession;
use App\Models\Partner;
use App\Http\Requests\CloseDailyShopRequest;
use App\Actions\Consignment\StartDailyShopAction;
use App\Actions\Consignment\CloseDailyShopAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    /**
     * Render the POS dashboard.
     */
    public function createOpen(): Response
    {
        return Inertia::render('Pos/OpenShop', [
            'partners' => Partner::where('is_active', true)
                ->select(['id', 'name'])
                ->get()
        ]);
    }

    /**
     * Menyimpan data produk konsinyasi baru menggunakan StartDailyShopAction.
     * * @param Request $request
     * @param StartDailyShopAction $startDailyShopAction
     * @return RedirectResponse
     */
    public function storeOpen(Request $request, StartDailyShopAction $startDailyShopAction): RedirectResponse
    {
        // 1. Validasi Input Dasar
        // Kita validasi 'markup' hanya boleh 5, 10, atau 15 sesuai permintaan.
        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'product_name' => 'required|string|max:255',
            'initial_stock' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'markup' => 'required|in:5,10,15',
            'shop_session_id' => 'nullable|integer', // Optional, tergantung sistem sesi teman Anda
        ]);

        try {

            $dataForAction = [
                'partner_id' => $validated['partner_id'],
                'product_name' => $validated['product_name'],
                'initial_stock' => $validated['initial_stock'],
                'base_price' => $validated['base_price'],
                'markup_percentage' => (int) $validated['markup'],
                'shop_session_id' => $validated['shop_session_id'] ?? null,
            ];

            $startDailyShopAction->execute($request->user(), $dataForAction);

            return redirect()
                ->back()
                ->with('success', 'Produk ' . $validated['product_name'] . ' berhasil ditambahkan!');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function index(): Response
    {
        return Inertia::render('Pos/Dashboard');
    }

    /**
     * Show form to open shop (start daily consignments).
     */
    public function createOpen(): Response
    {
        $partners = Partner::where('is_active', true)->get();

        return Inertia::render('Pos/OpenShop', [
            'partners' => $partners,
        ]);
    }

    /**
     * Store new daily consignments.
     */
    public function storeOpen(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'product_name' => 'required|string|max:255',
            'initial_stock' => 'required|integer|min:0',
            'base_price' => 'required|numeric|min:0',
            'markup' => 'required|integer|min:0', // assuming markup is percentage e.g. 10 for 10%
        ]);

        $basePrice = $validated['base_price'];
        $markupPercent = $validated['markup'];

        // selling_price = base_price + (base_price * markup / 100)
        $sellingPrice = $basePrice + ($basePrice * $markupPercent / 100);

        DailyConsignment::create([
            'date' => now(), // or $request->date if we allow backdating
            'partner_id' => $validated['partner_id'],
            'product_name' => $validated['product_name'],
            'initial_stock' => $validated['initial_stock'],
            'base_price' => $basePrice,
            'markup_percentage' => $markupPercent,
            'selling_price' => $sellingPrice,
            'remaining_stock' => $validated['initial_stock'], // initially same as initial
            'quantity_sold' => 0,
            'total_revenue' => 0,
            'total_profit' => 0,
            'status' => 'open',
            'input_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('pos.dashboard')->with('success', 'Product added to daily supply.');
    }

    /**
     * Show form to close shop (reconcile daily consignments).
     */
    public function createClose(): Response
    {
        // Get today's open consignments
        $consignments = DailyConsignment::with('partner')
            ->whereDate('date', now())
            ->where('status', 'open')
            ->get();

        return Inertia::render('Pos/CloseShop', [
            'consignments' => $consignments,
        ]);
    }

    /**
     * Get daily consignment items for the current session (API endpoint).
     * //Clay-X
     * @param Request $request //Clay-X
     * @return \Illuminate\Http\JsonResponse //Clay-X
     */
    public function getDailyConsignments(Request $request) //Clay-X
    { //Clay-X
        // Clay-X: Ambil query parameters
        $date = $request->query('date'); //Clay-X
        $userId = $request->query('user_id'); //Clay-X

        // Clay-X: Fetch items dengan status != 'closed'
        $items = DailyConsignment::where('date', $date) //Clay-X
            ->where('input_by_user_id', $userId) //Clay-X
            ->where(function ($query) { //Clay-X
                // Clay-X: Exclude session record (yang punya start_cash tapi no items yet)
                $query->whereNotNull('product_name')->orWhere('status', '=', 'open'); //Clay-X
            }) //Clay-X
            ->where('status', '!=', 'closed') //Clay-X
            ->select([ //Clay-X
                'id',
                'product_name',
                'initial_stock',
                'remaining_stock', //Clay-X
                'selling_price',
                'base_price' //Clay-X
            ]) //Clay-X
            ->get(); //Clay-X

        // Clay-X: Return response JSON
        return response()->json($items); //Clay-X
    } //Clay-X

    /**
     * Update (Close) daily shop session using CloseDailyShopAction.
     *
     * @param CloseDailyShopRequest $request
     * @param ShopSession $shopSession
     * @param CloseDailyShopAction $closeDailyShopAction
     * @return RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function updateClose(
        CloseDailyShopRequest $request,
        ShopSession $shopSession,
        CloseDailyShopAction $closeDailyShopAction
    ): RedirectResponse|\Illuminate\Http\JsonResponse {
        // Pastikan session adalah shop session yang valid (belum closed)
        if ($shopSession->status === 'closed') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop session is already closed.',
                ], 422);
            }
            abort(422, 'Shop session is already closed.');
        }

        // Validasi bahwa shop_session_id dari request sesuai dengan route parameter
        $validated = $request->validated();
        if (isset($validated['shop_session_id']) && (int) $validated['shop_session_id'] !== $shopSession->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop session ID mismatch.',
                ], 422);
            }
            abort(422, 'Shop session ID mismatch.');
        }

        try {
            // Panggil action untuk menutup shop dan hitung profit
            $result = $closeDailyShopAction->execute(
                $shopSession,
                $validated['items'],
                (float) $validated['actual_cash']
            );

            // Return response dengan data profit jika request adalah JSON API
            if ($request->expectsJson()) {
                return response()->json($result);
            }

            // Return redirect dengan success message untuk form submission
            return redirect()->route('pos.dashboard')->with('success', $result['message']);
        } catch (\Exception $e) {
            // Handle exception dan return error response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            // Return redirect dengan error message
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
