<?php

namespace Winter\Dusk;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as ServiceProviderBase;
use Winter\Dusk\Handlers\UserController;

class ServiceProvider extends ServiceProviderBase
{
    /**
     * Indicates if loading of the provider is deferred.
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerConsoleCommands();
    }

    /**
     * Register helper routes for handling authentication.
     * @return void
     */
    protected function registerRoutes()
    {
        if (env('APP_ENV') !== 'dusk') {
            return;
        }

        Route::get('/_dusk/login/{userId}/{manager?}', [UserController::class, 'login'])
            ->middleware('web')
            ->name('dusk.login');

        Route::get('/_dusk/logout/{manager?}', [UserController::class, 'logout'])
            ->middleware('web')
            ->name('dusk.logout');

        Route::get('/_dusk/user/{manager?}', [UserController::class, 'user'])
            ->middleware('web')
            ->name('dusk.user');
    }

    /**
     * Registers the Dusk plugin commands.
     * @return void
     */
    protected function registerConsoleCommands()
    {
        $this->commands([
            \Winter\Dusk\Console\Dusk::class,
            \Winter\Dusk\Console\DuskFails::class,
            \Laravel\Dusk\Console\ChromeDriverCommand::class,
        ]);
    }
}
