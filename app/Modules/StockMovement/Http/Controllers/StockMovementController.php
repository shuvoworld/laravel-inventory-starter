<?php

namespace App\Modules\StockMovement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\StockMovement\Http\Requests\StoreStockMovementRequest;
use App\Modules\StockMovement\Http\Requests\UpdateStockMovementRequest;
use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\Products\Models\Product;
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
                $badges = [
                    'in' => 'badge-success',
                    'out' => 'badge-danger',
                    'adjustment' => 'badge-warning'
                ];
                $class = $badges[$item->type] ?? 'badge-secondary';
                $icon = $item->type === 'in' ? '↗' : ($item->type === 'out' ? '↙' : '⚖');
                return "<span class='badge {$class}'>{$icon} " . ucfirst($item->type) . "</span>";
            })
            ->addColumn('quantity_formatted', function (StockMovement $item) {
                $sign = $item->type === 'out' ? '-' : '+';
                $color = $item->type === 'out' ? 'text-danger' : 'text-success';
                if ($item->type === 'adjustment') {
                    $color = 'text-warning';
                }
                return "<span class='{$color}'>{$sign}" . number_format($item->quantity) . "</span>";
            })
            ->addColumn('reference_info', function (StockMovement $item) {
                if (!$item->reference_type) {
                    return '<span class="text-muted">Manual Entry</span>';
                }

                $labels = [
                    'sales_order' => 'Sales Order',
                    'purchase_order' => 'Purchase Order',
                    'sales_order_adjustment' => 'Sales Adjustment',
                    'purchase_order_adjustment' => 'Purchase Adjustment',
                    'stock_adjustment' => 'Stock Adjustment'
                ];

                $label = $labels[$item->reference_type] ?? ucfirst(str_replace('_', ' ', $item->reference_type));
                $badge_class = $item->reference_type === 'sales_order' || $item->reference_type === 'sales_order_adjustment'
                    ? 'badge-primary'
                    : ($item->reference_type === 'purchase_order' || $item->reference_type === 'purchase_order_adjustment'
                        ? 'badge-info'
                        : 'badge-secondary');

                $reference_id = $item->reference_id ? " #{$item->reference_id}" : '';
                return "<span class='badge {$badge_class}'>{$label}{$reference_id}</span>";
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
        return view('stock-movement::create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'reason' => 'required|string|max:255'
        ]);

        StockMovement::create([
            'product_id' => $request->product_id,
            'type' => $request->type,
            'quantity' => $request->quantity,
            'reference_type' => 'stock_adjustment',
            'reference_id' => null,
            'notes' => $request->reason . ($request->notes ? ' - ' . $request->notes : ''),
        ]);

        return redirect()->route('modules.stock-movement.index')->with('success', 'Stock adjustment created successfully.');
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
}
