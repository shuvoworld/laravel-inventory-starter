<?php

use App\Http\Controllers\Settings;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\ModulesRegistryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Module;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('public.home');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('dashboard/financial-summary', [DashboardController::class, 'financialSummary'])
    ->middleware(['auth'])
    ->name('dashboard.financial-summary');

// Language switching routes
Route::get('language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('api/language/current', [LanguageController::class, 'current'])->name('language.current');

Route::get('/me', function () {
    return view('me');
})->middleware(['auth'])->name('me');

Route::middleware(['auth'])->group(function () {
    Route::get('settings/profile', [Settings\ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::put('settings/profile', [Settings\ProfileController::class, 'update'])->name('settings.profile.update');
    Route::delete('settings/profile', [Settings\ProfileController::class, 'destroy'])->name('settings.profile.destroy');
    Route::get('settings/password', [Settings\PasswordController::class, 'edit'])->name('settings.password.edit');
    Route::put('settings/password', [Settings\PasswordController::class, 'update'])->name('settings.password.update');
    Route::get('settings/appearance', [Settings\AppearanceController::class, 'edit'])->name('settings.appearance.edit');

    // Demo protected routes using roles/permissions
    Route::get('admin/area', function () {
        return response('Welcome Admin area', 200);
    })->middleware('role:admin')->name('admin.area');

    // Admin: Roles & Permissions UX
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('permissions', [PermissionsController::class, 'index'])->name('permissions.index');
        Route::post('permissions/roles', [PermissionsController::class, 'createRole'])->name('permissions.roles.create');
        Route::delete('permissions/roles/{role}', [PermissionsController::class, 'deleteRole'])->name('permissions.roles.delete');
        Route::post('permissions/permissions', [PermissionsController::class, 'createPermission'])->name('permissions.permissions.create');
        Route::delete('permissions/permissions/{permission}', [PermissionsController::class, 'deletePermission'])->name('permissions.permissions.delete');
        Route::post('permissions/users/{user}/assign-role', [PermissionsController::class, 'assignRole'])->name('permissions.users.assign-role');
        Route::delete('permissions/users/{user}/roles/{role}', [PermissionsController::class, 'revokeRole'])->name('permissions.users.revoke-role');
        Route::post('permissions/roles/{role}/give-permission', [PermissionsController::class, 'givePermission'])->name('permissions.roles.give-permission');
        Route::delete('permissions/roles/{role}/permissions/{permission}', [PermissionsController::class, 'revokePermission'])->name('permissions.roles.revoke-permission');

        // Helper: Generate CRUD permission set for a module
        Route::post('permissions/modules', [PermissionsController::class, 'createModulePermissions'])->name('permissions.modules.create');

        // Module Dictionary (Modules Registry)
        Route::get('modules', [ModulesRegistryController::class, 'index'])->name('modules.index');
        Route::patch('modules/{module}/toggle', [ModulesRegistryController::class, 'toggle'])->name('modules.toggle');
    });
});

// Auto-load Web routes from all modules, but include only those marked active in the central registry.
$modulesPath = app_path('Modules');
if (is_dir($modulesPath)) {
    foreach (glob($modulesPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $moduleDir) {
        $name = basename($moduleDir);
        $kebab = \Illuminate\Support\Str::kebab($name);
        $isActive = true;
        try {
            if (DB::getSchemaBuilder()->hasTable('modules')) {
                $isActive = Module::isActive($kebab);
            }
        } catch (\Throwable $e) {
            $isActive = true; // fail-open if any DB issues
        }
        if (!$isActive) {
            continue;
        }
        $routesWeb = $moduleDir . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
        if (file_exists($routesWeb)) {
            require $routesWeb;
        }
    }
}

require __DIR__.'/auth.php';
