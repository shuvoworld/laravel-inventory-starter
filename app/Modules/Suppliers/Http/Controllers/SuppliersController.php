<?php

namespace App\Modules\Suppliers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Suppliers\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class SuppliersController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $suppliers = Supplier::select(['id', 'name', 'code', 'email', 'phone', 'contact_person', 'status', 'created_at']);

            return DataTables::of($suppliers)
                ->addColumn('actions', function ($supplier) {
                    return view('Suppliers::partials.actions', compact('supplier'))->render();
                })
                ->editColumn('status', function ($supplier) {
                    $badgeClass = $supplier->status === 'active' ? 'success' : 'secondary';
                    return "<span class='badge badge-{$badgeClass}'>" . ucfirst($supplier->status) . "</span>";
                })
                ->editColumn('created_at', function ($supplier) {
                    return $supplier->created_at->format('M j, Y');
                })
                ->rawColumns(['actions', 'status'])
                ->make(true);
        }

        return view('Suppliers::index');
    }

    public function create()
    {
        return view('Suppliers::create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string'
        ]);

        Supplier::create($request->all());

        return redirect()->route('modules.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('purchaseOrders');
        return view('Suppliers::show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('Suppliers::edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string'
        ]);

        $supplier->update($request->all());

        return redirect()->route('modules.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        // Check if supplier has purchase orders
        if ($supplier->purchaseOrders()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete supplier with existing purchase orders.'
            ], 422);
        }

        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully.'
        ]);
    }

    public function getActive(): JsonResponse
    {
        $suppliers = Supplier::active()
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return response()->json($suppliers);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q');

        $suppliers = Supplier::active()
            ->search($query)
            ->select('id', 'name', 'code', 'email', 'phone')
            ->limit(20)
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'text' => $supplier->name . ' (' . $supplier->code . ')',
                    'name' => $supplier->name,
                    'code' => $supplier->code,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone
                ];
            });

        return response()->json(['results' => $suppliers]);
    }
}