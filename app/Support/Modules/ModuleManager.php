<?php

namespace App\Support\Modules;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class ModuleManager
{
    protected string $modulesPath;

    public function __construct(protected Filesystem $files)
    {
        $this->modulesPath = app_path('Modules');
    }

    /**
     * Return list of discovered modules (folder names) as strings.
     * @return Collection<int, string>
     */
    public function list(): Collection
    {
        if (!is_dir($this->modulesPath)) {
            return collect();
        }
        $dirs = glob($this->modulesPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
        return collect($dirs)->map(fn ($dir) => basename($dir))->values();
    }

    public function path(string $module, string $subPath = ''): string
    {
        $path = $this->modulesPath . DIRECTORY_SEPARATOR . $module;
        if ($subPath !== '') {
            $path .= DIRECTORY_SEPARATOR . $subPath;
        }
        return $path;
    }

    public function viewNamespace(string $module): string
    {
        return Str::kebab($module);
    }

    public function permissionPrefix(string $module): string
    {
        return Str::of($module)->lower()->toString();
    }

    /** Ensure CRUD permissions exist for the given module name. */
    public function ensureCrudPermissions(string $module, ?string $guard = null): void
    {
        $guard = $guard ?: config('auth.defaults.guard', 'web');
        $prefix = $this->permissionPrefix($module);
        foreach (['view', 'create', 'edit', 'delete'] as $action) {
            Permission::firstOrCreate(['name' => $prefix . '.' . $action, 'guard_name' => $guard]);
        }
    }
}
