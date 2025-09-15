<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        App\Providers\ModulesServiceProvider::class,
        OwenIt\Auditing\AuditingServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
        // Append global auto flash success middleware to the web group
        if (method_exists($middleware, 'appendToGroup')) {
            $middleware->appendToGroup('web', \App\Http\Middleware\AutoFlashSuccess::class);
        } else {
            // Fallback for older API: try generic append
            $middleware->append(\App\Http\Middleware\AutoFlashSuccess::class);
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
