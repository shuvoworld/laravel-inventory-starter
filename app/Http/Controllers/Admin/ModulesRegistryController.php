<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModulesRegistryController extends Controller
{
    public function index(Request $request): View
    {
        $modules = Module::query()->orderBy('name')->get();
        return view('admin.modules.index', compact('modules'));
    }

    public function toggle(Module $module): RedirectResponse
    {
        $module->is_active = !$module->is_active;
        $module->save();
        return back()->with('success', __('Module ":name" :state', [
            'name' => $module->name,
            'state' => $module->is_active ? __('activated') : __('deactivated'),
        ]));
    }
}
