<?php namespace RainLab\Dusk;

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
        $this->registerConsoleCommands();
    }

    /**
     * Registers the Dusk plugin commands.
     * @return void
     */
    protected function registerConsoleCommands()
    {
        $this->commands([
            \RainLab\Dusk\Console\Dusk::class,
            \RainLab\Dusk\Console\DuskFails::class,
            \Laravel\Dusk\Console\ChromeDriverCommand::class,
        ]);
    }
}
