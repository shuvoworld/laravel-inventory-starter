<?php

use Illuminate\Support\Str;

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
