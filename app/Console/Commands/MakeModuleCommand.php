<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleManager;
use Illuminate\Console\Attributes\AsCommand;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

#[AsCommand(name: 'module:make', description: 'Scaffold a new Module (model, migration, controller, policy, views, routes)')]
class MakeModuleCommand extends Command
{
    protected $signature = 'module:make {name : The Module name (StudlyCase)} {--force : Overwrite existing files}';

    protected $description = 'Scaffold a new Module (model, migration, controller, policy, views, routes)';

    public function handle(Filesystem $files, ModuleManager $modules): int
    {
        $name = Str::studly($this->argument('name'));
        $kebab = Str::kebab($name);
        $snake = Str::snake($name);
        $table = Str::plural($snake);
        $force = (bool)$this->option('force');

        $basePath = app_path('Modules' . DIRECTORY_SEPARATOR . $name);
        $structure = [
            'routes' => ['web.php', 'api.php'],
            'Http/Controllers' => ["{$name}Controller.php"],
            'Models' => ["{$name}.php"],
            'Policies' => ["{$name}Policy.php"],
            'Http/Requests' => ["Store{$name}Request.php", "Update{$name}Request.php"],
            'Http/Resources' => ["{$name}Resource.php"],
            'database/migrations' => [],
            'resources/views' => ['index.blade.php', 'create.blade.php', 'edit.blade.php', 'show.blade.php'],
            'resources/views/partials' => ['actions.blade.php'],
        ];

        // Create directories
        foreach (array_keys($structure) as $dir) {
            $fullDir = $basePath . DIRECTORY_SEPARATOR . $dir;
            $files->ensureDirectoryExists($fullDir);
        }

        // Publish files from stubs
        $stubBase = base_path('stubs/module');
        foreach ($structure as $dir => $filesList) {
            foreach ($filesList as $file) {
                $target = $basePath . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file;
                $stub = $stubBase . DIRECTORY_SEPARATOR . $file . '.stub';
                if (!$files->exists($stub)) {
                    // Some files have dynamic names
                    if (Str::endsWith($file, 'Controller.php')) $stub = $stubBase . DIRECTORY_SEPARATOR . 'Controller.php.stub';
                    if (Str::endsWith($file, 'Policy.php')) $stub = $stubBase . DIRECTORY_SEPARATOR . 'Policy.php.stub';
                    if (Str::endsWith($file, 'Request.php')) $stub = $stubBase . DIRECTORY_SEPARATOR . 'Request.php.stub';
                    if (Str::endsWith($file, 'Resource.php')) $stub = $stubBase . DIRECTORY_SEPARATOR . 'Resource.php.stub';
                    // Model mapping
                    if ($dir === 'Models' && Str::endsWith($file, '.php')) $stub = $stubBase . DIRECTORY_SEPARATOR . 'Model.php.stub';
                    // Blade views map by their basename
                    if (Str::endsWith($file, '.blade.php')) $stub = $stubBase . DIRECTORY_SEPARATOR . basename($file) . '.stub';
                    if (basename($file) === 'web.php') $stub = $stubBase . DIRECTORY_SEPARATOR . 'web.php.stub';
                    if (basename($file) === 'api.php') $stub = $stubBase . DIRECTORY_SEPARATOR . 'api.php.stub';
                }
                if (!$force && $files->exists($target)) {
                    $this->warn("Skip existing: " . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $target));
                    continue;
                }
                $contents = $files->get($stub);
                $contents = str_replace([
                    '{{ moduleStudly }}', '{{ moduleKebab }}', '{{ moduleSnake }}', '{{ moduleTable }}'
                ], [
                    $name, $kebab, $snake, $table
                ], $contents);
                $files->put($target, $contents);
                $this->info('Created: ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $target));
            }
        }

        // Create migration file with timestamp
        $migrationStub = $stubBase . DIRECTORY_SEPARATOR . 'migration.php.stub';
        $migrationName = date('Y_m_d_His') . "_create_{$table}_table.php";
        $migrationTarget = $basePath . DIRECTORY_SEPARATOR . 'database/migrations' . DIRECTORY_SEPARATOR . $migrationName;
        $migrationContents = $files->get($migrationStub);
        $migrationContents = str_replace(['{{ moduleTable }}', '{{ moduleStudly }}'], [$table, $name], $migrationContents);
        $files->put($migrationTarget, $migrationContents);
        $this->info('Created: ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $migrationTarget));

        // Ensure CRUD permissions exist
        $modules->ensureCrudPermissions($kebab);

        $this->line('Module scaffold complete. Next steps:');
        $this->line("- composer dump-autoload");
        $this->line("- php artisan migrate");
        $this->line("- Assign permissions to roles as needed");

        return self::SUCCESS;
    }
}
