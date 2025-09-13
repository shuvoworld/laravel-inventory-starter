<?php

namespace App\Support\Modules;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Base policy mapping standard CRUD abilities to Spatie permission checks.
 * Concrete module policies can extend this and set $module to the permission prefix.
 */
abstract class BaseModulePolicy
{
    /** e.g., 'posts', 'authors' */
    protected string $module;

    public function __construct()
    {
        if (!isset($this->module)) {
            // Try to infer from concrete class name: FooPolicy => foo
            $short = class_basename(static::class);
            $module = strtolower(preg_replace('/Policy$/', '', $short));
            $this->module = $module;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->can($this->module . '.view');
    }

    public function view(User $user, Model $model): bool
    {
        return $user->can($this->module . '.view');
    }

    public function create(User $user): bool
    {
        return $user->can($this->module . '.create');
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can($this->module . '.edit');
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->can($this->module . '.delete');
    }
}
