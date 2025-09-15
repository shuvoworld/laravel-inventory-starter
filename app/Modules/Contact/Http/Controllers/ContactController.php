<?php

namespace App\Modules\Contact\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Contact\Http\Requests\StoreContactRequest;
use App\Modules\Contact\Http\Requests\UpdateContactRequest;
use App\Modules\Contact\Models\Contact;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * Controller for managing Contacts CRUD and DataTables endpoint.
 */
class ContactController extends Controller
{
    public function index(Request $request): View
    {
        return view('contact::index');
    }

    /** DataTables server-side endpoint (Yajra) */
    public function data(Request $request)
    {
        $query = Contact::query();

        return DataTables::eloquent($query)
            ->addColumn('actions', function (Contact $contact) {
                return view('contact::partials.actions', ['id' => $contact->id])->render();
            })
            ->editColumn('created_at', function (Contact $contact) {
                return $contact->created_at?->toDateTimeString();
            })
            ->editColumn('updated_at', function (Contact $contact) {
                return $contact->updated_at?->toDateTimeString();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create(): View
    {
        $users = User::query()->select('id','name')->orderBy('name')->get();
        return view('contact::create', compact('users'));
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $item = Contact::create($request->validated());
        return redirect()->route('modules.contact.index')->with('success', 'Contact created.');
    }

    public function show(int $id): View
    {
        $item = Contact::findOrFail($id);
        return view('contact::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = Contact::findOrFail($id);
        return view('contact::edit', compact('item'));
    }

    public function update(UpdateContactRequest $request, int $id): RedirectResponse
    {
        $item = Contact::findOrFail($id);
        $item->update($request->validated());
        return redirect()->route('modules.contact.index')->with('success', 'Contact updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = Contact::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.contact.index')->with('success', 'Contact deleted.');
    }
}
