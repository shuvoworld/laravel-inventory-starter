<?php

namespace App\Modules\StockMovement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\StockMovement\Http\Requests\StoreStockMovementRequest;
use App\Modules\StockMovement\Http\Requests\UpdateStockMovementRequest;
use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\Products\Models\Product;
use App\Services\StockMovementService;
use App\Services\StockMovementReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * Controller for managing StockMovement CRUD pages and DataTables endpoint.
 */
class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        return view('stock-movement::index');
    }

    /** DataTables server-side endpoint (Yajra) */
    public function data(Request $request)
    {
        $query = StockMovement::with('product')->latest();

        return DataTables::eloquent($query)
            ->addColumn('type_badge', function (StockMovement $item) {
                // Use movement_type, fall back to type for backward compatibility
                $movementType = $item->movement_type ?? $item->type ?? 'adjustment';

                $badges = [
                    'in' => 'bg-success bg-opacity-25 text-success border border-success-subtle',
                    'out' => 'bg-danger bg-opacity-25 text-danger border border-danger-subtle',
                    'adjustment' => 'bg-warning bg-opacity-25 text-warning border border-warning-subtle'
                ];
                $class = $badges[$movementType] ?? 'bg-secondary bg-opacity-25 text-secondary';

                // Add more descriptive icons and text
                $icons = [
                    'in' => '<i class="fas fa-plus-circle me-1"></i>',
                    'out' => '<i class="fas fa-minus-circle me-1"></i>',
                    'adjustment' => '<i class="fas fa-balance-scale me-1"></i>'
                ];
                $icon = $icons[$movementType] ?? '<i class="fas fa-question-circle me-1"></i>';

                $text = ucfirst($movementType);
                return "<span class='badge {$class} fw-semibold px-3 py-2'>{$icon}{$text}</span>";
            })
            ->addColumn('quantity_formatted', function (StockMovement $item) {
                // Use movement_type, fall back to type for backward compatibility
                $movementType = $item->movement_type ?? $item->type ?? 'adjustment';

                if ($movementType === 'in') {
                    return "<span class='text-success fw-bold'>+ " . number_format($item->quantity) . "</span>";
                } elseif ($movementType === 'out') {
                    return "<span class='text-danger fw-bold'>- " . number_format($item->quantity) . "</span>";
                } else {
                    return "<span class='text-warning fw-bold'>⚖ " . number_format($item->quantity) . "</span>";
                }
            })
            ->addColumn('reference_info', function (StockMovement $item) {
                $transactionType = $item->transaction_type;
                $referenceType = $item->reference_type;

                // Get transaction type with emojis from model
                $allTransactionTypes = StockMovement::getTransactionTypes();
                $transactionLabel = $allTransactionTypes[$transactionType] ?? ucfirst($transactionType);

                // Determine movement direction for color coding
                $movementDirection = StockMovement::getMovementDirection($transactionType);

                // Color code based on movement direction
                $directionColors = [
                    'in' => 'bg-success bg-opacity-25 text-success border border-success-subtle',
                    'out' => 'bg-danger bg-opacity-25 text-danger border border-danger-subtle',
                    'adjustment' => 'bg-warning bg-opacity-25 text-warning border border-warning-subtle'
                ];

                $colorClass = $directionColors[$movementDirection] ?? 'bg-secondary bg-opacity-25 text-secondary';

                // Add direction icons
                $directionIcons = [
                    'in' => '<i class="fas fa-plus-circle me-1"></i>',
                    'out' => '<i class="fas fa-minus-circle me-1"></i>',
                    'adjustment' => '<i class="fas fa-balance-scale me-1"></i>'
                ];

                $directionIcon = $directionIcons[$movementDirection] ?? '';

                // Add special styling for critical movements
                $criticalTypes = ['damage', 'lost_missing', 'theft', 'expired', 'quality_control'];
                if (in_array($transactionType, $criticalTypes)) {
                    $colorClass = 'bg-danger bg-opacity-25 text-danger border border-danger-subtle fw-bold';
                }

                $reference_id = $item->reference_id ? " #{$item->reference_id}" : '';
                $displayText = strip_tags($transactionLabel) . $reference_id;

                return "<span class='badge {$colorClass} fw-semibold px-3 py-2'>{$directionIcon}{$displayText}</span>";
            })
            ->addColumn('actions', function (StockMovement $item) {
                return view('stock-movement::partials.actions', ['id' => $item->id])->render();
            })
            ->editColumn('created_at', function (StockMovement $item) {
                return $item->created_at?->format('M d, Y H:i');
            })
            ->rawColumns(['actions', 'type_badge', 'quantity_formatted', 'reference_info'])
            ->toJson();
    }

    public function create(): View
    {
        $products = Product::orderBy('name')->get();
        $transactionTypesByDirection = StockMovement::getTransactionTypesByDirection();
        $currentStock = [];

        foreach ($products as $product) {
            $currentStock[$product->id] = StockMovement::getCurrentStockFromMovements($product->id);
        }

        return view('stock-movement::create', compact('products', 'transactionTypesByDirection', 'currentStock'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'transaction_type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
            'reference_number' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255'
        ]);

        // Get all valid transaction types
        $validTransactionTypes = array_keys(StockMovement::getTransactionTypes());

        if (!in_array($request->transaction_type, $validTransactionTypes)) {
            return back()->withInput()->withErrors([
                'transaction_type' => 'Invalid transaction type selected.'
            ]);
        }

        // Auto-determine movement type from transaction type
        $movementType = StockMovement::getMovementDirection($request->transaction_type);

        // If outbound movement, validate stock availability (except for specific types)
        $skipStockValidation = ['damage', 'lost_missing', 'theft', 'expired', 'quality_control'];

        if ($movementType === 'out' && !in_array($request->transaction_type, $skipStockValidation)) {
            if (!StockMovementService::validateStockAvailability($request->product_id, $request->quantity)) {
                return back()->withInput()->withErrors([
                    'quantity' => 'Insufficient stock available. Current stock: ' . StockMovementService::getCurrentStock($request->product_id)
                ]);
            }
        }

        // Build notes
        $notes = $request->reason ?? '';
        if ($request->notes) {
            $notes .= ($notes ? ' - ' : '') . $request->notes;
        }

        // Use appropriate service method based on transaction type
        switch ($request->transaction_type) {
            // IN transactions
            case 'purchase':
                StockMovementService::recordPurchase($request->product_id, $request->quantity, null, $notes);
                break;
            case 'sale_return':
                StockMovementService::recordSaleReturn($request->product_id, $request->quantity, null, $notes);
                break;
            case 'opening_stock':
                StockMovementService::recordOpeningStock($request->product_id, $request->quantity, $notes);
                break;
            case 'transfer_in':
                StockMovementService::recordTransferIn($request->product_id, $request->quantity, null, $notes);
                break;
            case 'stock_count_correction':
                StockMovementService::recordStockCountCorrectionPlus($request->product_id, $request->quantity, $notes);
                break;
            case 'recovery_found':
                StockMovementService::recordRecovery($request->product_id, $request->quantity, $notes);
                break;
            case 'manufacturing_in':
                StockMovementService::recordManufacturingIn($request->product_id, $request->quantity, null, $notes);
                break;

            // OUT transactions
            case 'sale':
                StockMovementService::recordSale($request->product_id, $request->quantity, null, $notes);
                break;
            case 'purchase_return':
                StockMovementService::recordPurchaseReturn($request->product_id, $request->quantity, null, $notes);
                break;
            case 'damage':
                StockMovementService::recordDamage($request->product_id, $request->quantity, $notes);
                break;
            case 'lost_missing':
                StockMovementService::recordLost($request->product_id, $request->quantity, $notes);
                break;
            case 'theft':
                StockMovementService::recordTheft($request->product_id, $request->quantity, $notes);
                break;
            case 'expired':
                StockMovementService::recordExpired($request->product_id, $request->quantity, $notes);
                break;
            case 'transfer_out':
                StockMovementService::recordTransferOut($request->product_id, $request->quantity, null, $notes);
                break;
            case 'stock_count_correction_minus':
                StockMovementService::recordStockCountCorrectionMinus($request->product_id, $request->quantity, $notes);
                break;
            case 'quality_control':
                StockMovementService::recordQualityControl($request->product_id, $request->quantity, $notes);
                break;
            case 'manufacturing_out':
                StockMovementService::recordManufacturingOut($request->product_id, $request->quantity, null, $notes);
                break;
            case 'promotional':
                StockMovementService::recordPromotional($request->product_id, $request->quantity, $notes);
                break;

            // Default
            default:
                StockMovementService::recordAdjustment($request->product_id, $movementType, $request->quantity, $notes);
                break;
        }

        return redirect()->route('modules.stock-movement.index')
            ->with('success', 'Stock movement created successfully. Transaction type: ' . StockMovement::getTransactionTypes()[$request->transaction_type]);
    }

    public function show(int $id): View
    {
        $item = StockMovement::findOrFail($id);
        return view('stock-movement::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = StockMovement::findOrFail($id);
        return view('stock-movement::edit', compact('item'));
    }

    public function update(UpdateStockMovementRequest $request, int $id): RedirectResponse
    {
        $item = StockMovement::findOrFail($id);
        $item->update($request->validated());
        return redirect()->route('modules.stock-movement.index')->with('success', 'StockMovement updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = StockMovement::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.stock-movement.index')->with('success', 'StockMovement deleted.');
    }

    /**
     * Display stock movement report page
     */
    public function report(Request $request): View
    {
        $filters = $request->only(['start_date', 'end_date', 'product_id', 'movement_type', 'transaction_type']);
        $products = Product::orderBy('name')->pluck('name', 'id');
        $movementTypes = StockMovement::getMovementTypes();
        $transactionTypes = StockMovement::getTransactionTypes();

        $report = StockMovementReportService::generateReport($filters);

        return view('stock-movement::report', compact(
            'report', 'filters', 'products', 'movementTypes', 'transactionTypes'
        ));
    }

    /**
     * Display product stock history
     */
    public function productHistory(Request $request, int $productId): View
    {
        $product = Product::findOrFail($productId);
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $history = StockMovementReportService::getProductHistory(
            $productId,
            \Carbon\Carbon::parse($startDate),
            \Carbon\Carbon::parse($endDate)
        );

        return view('stock-movement::product-history', compact('history', 'product', 'startDate', 'endDate'));
    }

    /**
     * Display audit trail for a movement
     */
    public function auditTrail(int $id): View
    {
        $auditData = StockMovementReportService::getAuditTrail($id);
        return view('stock-movement::audit-trail', compact('auditData'));
    }

    /**
     * Export stock movements to CSV
     */
    public function export(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'product_id', 'movement_type', 'transaction_type']);
        $csv = StockMovementReportService::exportToCsv($filters);

        $filename = 'stock_movements_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Display inventory valuation report
     */
    public function valuation(): View
    {
        $valuation = StockMovementReportService::getInventoryValuation();
        return view('stock-movement::valuation', compact('valuation'));
    }

    /**
     * Display stock movement trends
     */
    public function trends(Request $request): View
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $trends = StockMovementReportService::getDailyTrends(
            \Carbon\Carbon::parse($startDate),
            \Carbon\Carbon::parse($endDate)
        );

        return view('stock-movement::trends', compact('trends', 'startDate', 'endDate'));
    }

    /**
     * Display stock reconciliation page
     */
    public function reconcile(): View
    {
        $products = Product::orderBy('name')->get();
        $discrepancies = [];

        foreach ($products as $product) {
            $systemStock = $product->quantity_on_hand;
            $movementStock = StockMovement::getCurrentStockFromMovements($product->id);

            if ($systemStock !== $movementStock) {
                $discrepancies[] = [
                    'product' => $product,
                    'system_stock' => $systemStock,
                    'movement_stock' => $movementStock,
                    'difference' => $systemStock - $movementStock,
                    'movements' => StockMovement::getProductMovements($product->id, 10)
                ];
            }
        }

        return view('stock-movement::reconcile', compact('products', 'discrepancies'));
    }

    /**
     * Process stock reconciliation
     */
    public function processReconciliation(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'actual_count' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        $product = Product::findOrFail($request->product_id);
        $actualCount = $request->actual_count;
        $currentStock = StockMovement::getCurrentStockFromMovements($product->id);
        $difference = $actualCount - $currentStock;

        if ($difference !== 0) {
            // Record adjustment movement
            $transactionType = $difference > 0 ? 'stock_count_correction' : 'stock_count_correction_minus';
            $notes = $request->notes ?: "Stock reconciliation - Actual count: {$actualCount}, System: {$currentStock}, Difference: {$difference}";

            if ($difference > 0) {
                StockMovementService::recordStockCountCorrectionPlus($product->id, abs($difference), $notes);
            } else {
                StockMovementService::recordStockCountCorrectionMinus($product->id, abs($difference), $notes);
            }

            // Update product quantity to match movements
            $product->quantity_on_hand = StockMovement::getCurrentStockFromMovements($product->id);
            $product->save();
        }

        return redirect()->route('modules.stock-movement.reconcile')
            ->with('success', "Stock reconciliation completed for {$product->name}. Adjustment: " . ($difference >= 0 ? '+' : '') . $difference);
    }

    /**
     * Get stock count sheet for physical counting
     */
    public function countSheet(): View
    {
        $products = Product::orderBy('name')->get();
        $stockData = [];

        foreach ($products as $product) {
            $stockData[$product->id] = [
                'product' => $product,
                'current_stock' => StockMovement::getCurrentStockFromMovements($product->id),
                'last_movement' => StockMovement::where('product_id', $product->id)->latest()->first(),
                'category' => $product->category,
                'location' => 'Warehouse A', // You can add location field to products
            ];
        }

        return view('stock-movement::count-sheet', compact('stockData'));
    }

    /**
     * API endpoint to get current stock from movements
     */
    public function getStockFromMovements(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $productId = $request->product_id;
        $currentStock = StockMovement::getCurrentStockFromMovements($productId);
        $product = Product::findOrFail($productId);

        return response()->json([
            'product_id' => $productId,
            'product_name' => $product->name,
            'system_stock' => $product->quantity_on_hand,
            'movement_stock' => $currentStock,
            'discrepancy' => $product->quantity_on_hand - $currentStock,
            'last_updated' => StockMovement::where('product_id', $productId)->latest()->value('created_at')
        ]);
    }
}
