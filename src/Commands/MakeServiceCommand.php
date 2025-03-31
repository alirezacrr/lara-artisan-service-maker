<?php

namespace Alireza\LaraArtisanServiceMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name : The name of the service}
                           {--i|interface : Create an interface for this service}
                           {--m|model= : The model that the service will use}
                           {--b|bind : Automatically bind the service in the service provider}
                           {--s|singleton : Register the service as a singleton}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $createInterface = $this->option('interface');
        $model = $this->option('model');
        $bind = $this->option('bind');
        $singleton = $this->option('singleton');
        $servicePath = app_path('Services');
        
        // Create base Services directory if it doesn't exist
        if (!File::exists($servicePath)) {
            File::makeDirectory($servicePath, 0755, true);
        }
        
        // Handle nested directories in the name
        $serviceClass = class_basename($name);
        $serviceNamespace = 'App\\Services';
        $interfaceNamespace = 'App\\Interfaces';
        
        // If name contains directory separators
        if (str_contains($name, '/') || str_contains($name, '\\')) {
            $parts = explode('/', str_replace('\\', '/', $name));
            $serviceClass = array_pop($parts);
            $directory = implode('/', $parts);
            
            // Create the full directory path
            $fullPath = $servicePath . '/' . $directory;
            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
            }
            
            $serviceFile = $fullPath . '/' . $serviceClass . 'Service.php';
            $serviceNamespace .= '\\' . str_replace('/', '\\', $directory);
            $interfaceNamespace .= '\\' . str_replace('/', '\\', $directory);
        } else {
            $serviceFile = $servicePath . '/' . $serviceClass . 'Service.php';
        }
        
        if (File::exists($serviceFile)) {
            $this->error('Service already exists!');
            return 1;
        }
        
        // Create interface if requested
        if ($createInterface) {
            $this->call('make:interface', [
                'name' => $name . 'Service'
            ]);
        }
        
        // Prepare the service stub
        $useStatements = '';
        $implements = '';
        $properties = '';
        $constructor = "    /**\n     * Create a new service instance.\n     */\n    public function __construct()\n    {\n        //\n    }";
        
        // Add interface implementation if requested
        if ($createInterface) {
            $useStatements .= "use {$interfaceNamespace}\\{$serviceClass}ServiceInterface;\n";
            $implements = " implements {$serviceClass}ServiceInterface";
        }
        
        // Add model if provided
        if ($model) {
            $useStatements .= "use App\\Models\\{$model};\n";
            $properties = "    /**\n     * @var {$model}\n     */\n    protected \$model;\n\n";
            $constructor = "    /**\n     * Create a new service instance.\n     */\n    public function __construct({$model} \$model)\n    {\n        \$this->model = \$model;\n    }";
        }
        
        $stub = <<<EOT
<?php

namespace {$serviceNamespace};

{$useStatements}
class {$serviceClass}Service{$implements}
{
{$properties}{$constructor}
}
EOT;
        
        File::put($serviceFile, $stub);
        
        $this->info('Service created successfully: ' . $name . 'Service');
        
        // Handle service binding if requested
        if ($bind || $singleton) {
            $this->registerServiceBinding($serviceNamespace, $serviceClass, $createInterface, $interfaceNamespace, $singleton);
        }
        
        return 0;
    }

    /**
     * Register the service binding in the AppServiceProvider.
     *
     * @param string $serviceNamespace
     * @param string $serviceClass
     * @param bool $hasInterface
     * @param string $interfaceNamespace
     * @param bool $singleton
     * @return void
     */
    protected function registerServiceBinding($serviceNamespace, $serviceClass, $hasInterface, $interfaceNamespace, $singleton)
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
        $fullServiceClass = "{$serviceNamespace}\\{$serviceClass}Service";
        $bindMethod = $singleton ? 'singleton' : 'bind';
        
        if ($hasInterface) {
            $fullInterfaceClass = "{$interfaceNamespace}\\{$serviceClass}ServiceInterface";
            $bindingCode = "\$this->app->{$bindMethod}({$serviceClass}ServiceInterface::class, {$serviceClass}Service::class);";
            
            // Add use statement for interface if not exists
            if (!str_contains($providerContent, "use {$fullInterfaceClass};")) {
                $providerContent = preg_replace(
                    '/(namespace App\\\\Providers;.*?)(\s*use|\s*class)/s',
                    "$1\nuse {$fullInterfaceClass};$2",
                    $providerContent
                );
            }
        } else {
            $bindingCode = "\$this->app->{$bindMethod}({$serviceClass}Service::class);";
        }
        
        // Add use statement for service if not exists
        if (!str_contains($providerContent, "use {$fullServiceClass};")) {
            $providerContent = preg_replace(
                '/(namespace App\\\\Providers;.*?)(\s*use|\s*class)/s',
                "$1\nuse {$fullServiceClass};$2",
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
        
        $this->info('Service binding added to AppServiceProvider: ' . ($hasInterface ? 
            "{$serviceClass}ServiceInterface => {$serviceClass}Service" : 
            "{$serviceClass}Service") . ' as ' . ($singleton ? 'singleton' : 'transient'));
    }
}
