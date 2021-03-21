<?php namespace Winter\Dusk;

use Route;
use Illuminate\Support\ServiceProvider as ServiceProviderBase;

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
        Route::get('/_dusk/login/{userId}/{manager?}', [
            'middleware' => 'web',
            'uses' => '\Winter\Dusk\Controllers\UserController@login',
        ]);

        Route::get('/_dusk/logout/{manager?}', [
            'middleware' => 'web',
            'uses' => '\Winter\Dusk\Controllers\UserController@logout',
        ]);

        Route::get('/_dusk/user/{manager?}', [
            'middleware' => 'web',
            'uses' => '\Winter\Dusk\Controllers\UserController@user',
        ]);
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
