<?php

namespace App\Providers;

use App\Support\Modules\ModuleManager;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ModulesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind the ModuleManager singleton
        $this->app->singleton(ModuleManager::class, fn($app) => new ModuleManager($app['files']));
    }

    public function boot(): void
    {
        $modulesPath = app_path('Modules');
        if (!is_dir($modulesPath)) {
            return; // No modules folder yet
        }

        foreach (glob($modulesPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $moduleDir) {
            $name = basename($moduleDir);
            // Skip deprecated demo modules
            if (in_array($name, ['Posts', 'Authors'], true)) {
                continue;
            }

            // Sync into central modules registry table (fail-open if table missing)
            try {
                /** @var \Illuminate\Database\Schema\Builder $schema */
                $schema = $this->app['db']->getSchemaBuilder();
                if ($schema->hasTable('modules')) {
                    $kebab = \Illuminate\Support\Str::kebab($name);
                    \App\Models\Module::query()->updateOrCreate(
                        ['name' => $kebab],
                        [
                            'namespace' => $name,
                            'title' => str_replace('-', ' ', ucfirst($kebab)),
                            'path' => $moduleDir,
                            // do not override is_active if entry exists
                        ]
                    );
                }
            } catch (\Throwable $e) {
                // ignore errors so boot continues
            }

            $this->bootModule($name, $moduleDir);
        }
    }

    protected function bootModule(string $name, string $path): void
    {
        // Routes are now loaded centrally from routes/web.php and routes/api.php to be cache-friendly.
        // (Avoid loading here to prevent duplicate registrations.)

        // Views
        $viewsPath = $path . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, Str::kebab($name));
        }

        // Migrations
        $migrationsPath = $path . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }

        // Translations
        $langPath = $path . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang';
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, Str::kebab($name));
        }

        // Auto-register Policies if present: app/Modules/{Module}/Policies/*Policy.php
        $policiesPath = $path . DIRECTORY_SEPARATOR . 'Policies';
        if (is_dir($policiesPath)) {
            foreach (glob($policiesPath . DIRECTORY_SEPARATOR . '*Policy.php') as $policyFile) {
                $policyClass = 'App\\Modules\\' . $name . '\\Policies\\' . basename($policyFile, '.php');
                // Infer Model class by replacing "Policy" suffix with model name in Models namespace
                $modelBase = str_replace('Policy', '', basename($policyFile, '.php'));
                $modelClass = 'App\\Modules\\' . $name . '\\Models\\' . $modelBase;
                if (class_exists($policyClass) && class_exists($modelClass)) {
                    Gate::policy($modelClass, $policyClass);
                }
            }
        }
    }
}
