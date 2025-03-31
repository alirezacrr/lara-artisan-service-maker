<?php

namespace Alireza\LaraArtisanServiceMaker;

use Alireza\LaraArtisanServiceMaker\Commands\MakeTraitCommand;
use Alireza\LaraArtisanServiceMaker\Commands\MakeInterfaceCommand;
use Alireza\LaraArtisanServiceMaker\Commands\MakeServiceCommand;
use Alireza\LaraArtisanServiceMaker\Commands\MakeRepositoryCommand;
use Illuminate\Support\ServiceProvider;

class ServiceMakerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeServiceCommand::class,
                MakeInterfaceCommand::class,
                MakeRepositoryCommand::class,
                MakeTraitCommand::class,
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}