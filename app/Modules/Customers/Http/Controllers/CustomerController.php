<?php

namespace App\Modules\Customers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Customers\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('customers::index');
    }

    public function data(Request $request)
    {
        $query = Customer::query();

        return DataTables::eloquent($query)
            ->addColumn('actions', function (Customer $customer) {
                return view('customers::partials.actions', ['id' => $customer->id])->render();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('customers::create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country' => $validated['country'] ?? null,
        ]);

        return redirect()->route('modules.customers.index')->with('success', 'Customer created');
    }

    public function show(int $id): View
    {
        $item = Customer::findOrFail($id);

        return view('customers::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = Customer::findOrFail($id);

        return view('customers::edit', compact('item'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email,'.$item->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $item->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country' => $validated['country'] ?? null,
        ]);

        return redirect()->route('modules.customers.index')->with('success', 'Customer updated');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = Customer::findOrFail($id);
        $item->delete();

        return redirect()->route('modules.customers.index')->with('success', 'Customer deleted');
    }
}