<?php

namespace Alireza\LaraArtisanServiceMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeTraitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:trait {name : The name of the trait}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new trait';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $traitPath = app_path('Traits');
        
        if (!File::exists($traitPath)) {
            File::makeDirectory($traitPath, 0755, true);
        }
        
        $traitFile = $traitPath . '/' . $name . '.php';
        
        if (File::exists($traitFile)) {
            $this->error('Trait already exists!');
            return 1;
        }
        
        $stub = <<<EOT
<?php

namespace App\Traits;

trait {$name}
{
    //
}
EOT;
        
        File::put($traitFile, $stub);
        
        $this->info('Trait created successfully: ' . $name);
        return 0;
    }
}
