<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Module;

// API routes
// Keep this file minimal and framework-agnostic. Module APIs (if any) are loaded below.

// Auto-load API routes from all modules to ensure they are present in route cache.
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
            $isActive = true; // fail-open
        }
        if (!$isActive) {
            continue;
        }
        $routesApi = $moduleDir . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';
        if (file_exists($routesApi)) {
            require $routesApi;
        }
    }
}
