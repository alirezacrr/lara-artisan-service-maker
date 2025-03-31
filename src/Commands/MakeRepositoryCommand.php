<?php

namespace Alireza\LaraArtisanServiceMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepositoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name : The name of the repository}
                            {--model= : The model that the repository will use}
                            {--i|interface : Create an interface for this repository}
                            {--b|bind : Automatically bind the repository in the service provider}
                            {--s|singleton : Register the repository as a singleton}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $model = $this->option('model') ?: $name;
        $createInterface = $this->option('interface');
        $bind = $this->option('bind');
        $singleton = $this->option('singleton');
        $repositoryPath = app_path('Repositories');

        // Create base Repositories directory if it doesn't exist
        if (!File::exists($repositoryPath)) {
            File::makeDirectory($repositoryPath, 0755, true);
        }

        // Handle nested directories in the name
        $repositoryClass = class_basename($name);
        $repositoryNamespace = 'App\\Repositories';
        $interfaceNamespace = 'App\\Interfaces';

        // If name contains directory separators
        if (str_contains($name, '/') || str_contains($name, '\\')) {
            $parts = explode('/', str_replace('\\', '/', $name));
            $repositoryClass = array_pop($parts);
            $directory = implode('/', $parts);

            // Create the full directory path
            $fullPath = $repositoryPath . '/' . $directory;
            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
            }

            $repositoryFile = $fullPath . '/' . $repositoryClass . 'Repository.php';
            $repositoryNamespace .= '\\' . str_replace('/', '\\', $directory);
            $interfaceNamespace .= '\\' . str_replace('/', '\\', $directory);
        } else {
            $repositoryFile = $repositoryPath . '/' . $repositoryClass . 'Repository.php';
        }

        if (File::exists($repositoryFile)) {
            $this->error('Repository already exists!');
            return 1;
        }

        // Create interface if requested
        if ($createInterface) {
            $this->call('make:interface', [
                'name' => $name . 'Repository'
            ]);
        }

        $stub = <<<EOT
<?php

namespace {$repositoryNamespace};

use App\Models\\{$model};
EOT;

        if ($createInterface) {
            $stub .= "\nuse {$interfaceNamespace}\\{$repositoryClass}RepositoryInterface;";
            $implements = " implements {$repositoryClass}RepositoryInterface";
        } else {
            $implements = "";
        }

        $stub .= <<<EOT

class {$repositoryClass}Repository{$implements}
{
    /**
     * @var {$model}
     */
    protected \$model;

    /**
     * Create a new repository instance.
     */
    public function __construct({$model} \$model)
    {
        \$this->model = \$model;
    }

    /**
     * Get all resources.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return \$this->model->all();
    }

    /**
     * Find resource by id.
     *
     * @param int \$id
     * @return \App\Models\\{$model}|null
     */
    public function find(\$id)
    {
        return \$this->model->find(\$id);
    }

    /**
     * Create new resource.
     *
     * @param array \$data
     * @return \App\Models\\{$model}
     */
    public function create(array \$data)
    {
        return \$this->model->create(\$data);
    }

    /**
     * Update resource.
     *
     * @param array \$data
     * @param int \$id
     * @return bool
     */
    public function update(array \$data, \$id)
    {
        \$record = \$this->find(\$id);
        return \$record->update(\$data);
    }

    /**
     * Delete resource.
     *
     * @param int \$id
     * @return bool
     */
    public function delete(\$id)
    {
        return \$this->model->destroy(\$id);
    }
}
EOT;

        File::put($repositoryFile, $stub);

        // Update the interface with matching methods if it was created
        if ($createInterface) {
            $interfaceFile = str_replace('Repositories', 'Interfaces', str_replace('Repository.php', 'RepositoryInterface.php', $repositoryFile));

            $interfaceStub = <<<EOT
<?php

namespace {$interfaceNamespace};

interface {$repositoryClass}RepositoryInterface
{
    /**
     * Get all resources.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();

    /**
     * Find resource by id.
     *
     * @param int \$id
     * @return mixed
     */
    public function find(\$id);

    /**
     * Create new resource.
     *
     * @param array \$data
     * @return mixed
     */
    public function create(array \$data);

    /**
     * Update resource.
     *
     * @param array \$data
     * @param int \$id
     * @return bool
     */
    public function update(array \$data, \$id);

    /**
     * Delete resource.
     *
     * @param int \$id
     * @return bool
     */
    public function delete(\$id);
}
EOT;

            File::put($interfaceFile, $interfaceStub);
        }

        $this->info('Repository created successfully: ' . $name . 'Repository');

        // Handle repository binding if requested
        if ($bind || $singleton) {
            $this->registerRepositoryBinding($repositoryNamespace, $repositoryClass, $createInterface, $interfaceNamespace, $singleton, $model);
        }

        return 0;
    }

    /**
     * Register the repository binding in the AppServiceProvider.
     *
     * @param string $repositoryNamespace
     * @param string $repositoryClass
     * @param bool $hasInterface
     * @param string $interfaceNamespace
     * @param bool $singleton
     * @param string $model
     * @return void
     */
    protected function registerRepositoryBinding($repositoryNamespace, $repositoryClass, $hasInterface, $interfaceNamespace, $singleton, $model)
    {
        $providerPath = app_path('Providers/AppServiceProvider.php');

        if (!File::exists($providerPath)) {
            $this->error('AppServiceProvider.php not found!');
            return;
        }

        $providerContent = File::get($providerPath);

        // Check if the register method exists
        if (!preg_match('/public function register\(\).*?{/s', $providerContent)) {
            $this->error('Could not find register method in AppServiceProvider!');
            return;
        }

        // Prepare the binding code
        $fullRepositoryClass = "{$repositoryNamespace}\\{$repositoryClass}Repository";
        $bindMethod = $singleton ? 'singleton' : 'bind';

        if ($hasInterface) {
            $fullInterfaceClass = "{$interfaceNamespace}\\{$repositoryClass}RepositoryInterface";
            $bindingCode = "\$this->app->{$bindMethod}({$repositoryClass}RepositoryInterface::class, {$repositoryClass}Repository::class);";

            // Add use statement for interface if not exists
            if (!str_contains($providerContent, "use {$fullInterfaceClass};")) {
                $providerContent = preg_replace(
                    '/(namespace App\\\\Providers;.*?)(\s*use|\s*class)/s',
                    "$1\nuse {$fullInterfaceClass};$2",
                    $providerContent
                );
            }
        } else {
            $bindingCode = "\$this->app->{$bindMethod}({$repositoryClass}Repository::class, function (\$app) {\n            return new \\{$fullRepositoryClass}(\$app->make(\\App\\Models\\{$model}::class));\n        });";
        }

        // Add use statement for repository if not exists
        if (!str_contains($providerContent, "use {$fullRepositoryClass};")) {
            $providerContent = preg_replace(
                '/(namespace App\\\\Providers;.*?)(\s*use|\s*class)/s',
                "$1\nuse {$fullRepositoryClass};$2",
                $providerContent
            );
        }

        // Add binding to register method
        $providerContent = preg_replace(
            '/(public function register\(\).*?{)(.*?)(})/s',
            "$1$2        {$bindingCode}\n$3",
            $providerContent
        );

        File::put($providerPath, $providerContent);

        $this->info('Repository binding added to AppServiceProvider: ' . ($hasInterface ?
            "{$repositoryClass}RepositoryInterface => {$repositoryClass}Repository" :
            "{$repositoryClass}Repository") . ' as ' . ($singleton ? 'singleton' : 'transient'));
    }
}
