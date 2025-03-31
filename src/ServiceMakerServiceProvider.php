<?php

namespace Alirezacrr\LaraArtisanServiceMaker;

use Alirezacrr\LaraArtisanServiceMaker\Commands\MakeTraitCommand;
use Alirezacrr\LaraArtisanServiceMaker\Commands\MakeInterfaceCommand;
use Alirezacrr\LaraArtisanServiceMaker\Commands\MakeServiceCommand;
use Alirezacrr\LaraArtisanServiceMaker\Commands\MakeRepositoryCommand;
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