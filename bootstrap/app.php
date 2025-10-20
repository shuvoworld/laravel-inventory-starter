<?php

use App\Http\Middleware\AutoFlashSuccess;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use App\Jobs\SyncProductStockFromMovements;

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
            'locale' => SetLocale::class,
        ]);
        // Append global middleware to the web group
        if (method_exists($middleware, 'appendToGroup')) {
            $middleware->appendToGroup('web', AutoFlashSuccess::class);
            $middleware->appendToGroup('web', SetLocale::class);
        } else {
            // Fallback for older API: try generic append
            $middleware->append(AutoFlashSuccess::class);
            $middleware->append(SetLocale::class);
        }
    })
    ->withSchedule(function (Schedule $schedule) {
        // Schedule hourly stock synchronization from movements using artisan command
        $schedule->command('product:sync-stock')
            ->hourly()
            ->description('Synchronize product stock quantities from stock movements')
            ->onSuccess(function () {
                \Log::info('Hourly stock synchronization completed successfully', [
                    'scheduled_at' => now()->format('Y-m-d H:i:s')
                ]);
            })
            ->onFailure(function () {
                \Log::error('Hourly stock synchronization failed', [
                    'scheduled_at' => now()->format('Y-m-d H:i:s')
                ]);
            });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
