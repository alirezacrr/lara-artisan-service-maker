<?php

namespace Alireza\LaraArtisanServiceMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeInterfaceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:interface {name : The name of the interface}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new interface';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $interfacePath = app_path('Interfaces');

        // Create base Interfaces directory if it doesn't exist
        if (!File::exists($interfacePath)) {
            File::makeDirectory($interfacePath, 0755, true);
        }

        // Handle nested directories in the name
        $interfaceClass = class_basename($name);
        $interfaceNamespace = 'App\\Interfaces';

        // If name contains directory separators
        if (str_contains($name, '/') || str_contains($name, '\\')) {
            $parts = explode('/', str_replace('\\', '/', $name));
            $interfaceClass = array_pop($parts);
            $directory = implode('/', $parts);

            // Create the full directory path
            $fullPath = $interfacePath . '/' . $directory;
            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
            }

            $interfaceFile = $fullPath . '/' . $interfaceClass . 'Interface.php';
            $interfaceNamespace .= '\\' . str_replace('/', '\\', $directory);
        } else {
            $interfaceFile = $interfacePath . '/' . $interfaceClass . 'Interface.php';
        }

        if (File::exists($interfaceFile)) {
            $this->error('Interface already exists!');
            return 1;
        }

        $stub = <<<EOT
<?php

namespace {$interfaceNamespace};

interface {$interfaceClass}Interface
{
    //
}
EOT;

        File::put($interfaceFile, $stub);

        $this->info('Interface created successfully: ' . $name . 'Interface');
        return 0;
    }
}
