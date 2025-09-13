<?php

namespace App\Modules\BlogCategory\Http\Controllers;

use App\Modules\BlogCategory\Http\Requests\StoreBlogCategoryRequest;
use App\Modules\BlogCategory\Http\Requests\UpdateBlogCategoryRequest;
use App\Modules\BlogCategory\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BlogCategoryController
{
    public function index(Request $request): View
    {
        return view('blog-category::index');
    }

    /** DataTables server-side endpoint (Yajra) */
    public function data(Request $request): Response
    {
        $query = BlogCategory::query();

        $dt = DataTables::eloquent($query)
            ->addColumn('actions', function ($row) {
                return view('blog-category::partials.actions', ['category' => $row])->render();
            })
            ->editColumn('created_at', function ($row) {
                return optional($row->created_at)->toDateTimeString();
            })
            ->editColumn('updated_at', function ($row) {
                return optional($row->updated_at)->toDateTimeString();
            })
            ->rawColumns(['actions'])
            ->toJson();

        return response($dt->getData(true));
    }

    public function create(): View
    {
        return view('blog-category::create');
    }

    public function store(StoreBlogCategoryRequest $request): RedirectResponse
    {
        $item = BlogCategory::create($request->validated());
        return redirect()->route('modules.blog-category.index')->with('status', 'BlogCategory created.');
    }

    public function show(int $id): View
    {
        $item = BlogCategory::findOrFail($id);
        return view('blog-category::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = BlogCategory::findOrFail($id);
        return view('blog-category::edit', compact('item'));
    }

    public function update(UpdateBlogCategoryRequest $request, int $id): RedirectResponse
    {
        $item = BlogCategory::findOrFail($id);
        $item->update($request->validated());
        return redirect()->route('modules.blog-category.index')->with('status', 'BlogCategory updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = BlogCategory::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.blog-category.index')->with('status', 'BlogCategory deleted.');
    }
}
