<?php

use Illuminate\Support\Facades\Route;

// API routes
// Keep this file minimal and framework-agnostic. Module APIs (if any) are loaded below.

// Auto-load API routes from all modules to ensure they are present in route cache.
$modulesPath = app_path('Modules');
if (is_dir($modulesPath)) {
    foreach (glob($modulesPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $moduleDir) {
        $routesApi = $moduleDir . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';
        if (file_exists($routesApi)) {
            require $routesApi;
        }
    }
}
