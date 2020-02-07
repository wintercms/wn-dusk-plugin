<?php namespace RainLab\Dusk;

use App;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'rainlab.dusk::lang.plugin.name',
            'description' => 'rainlab.dusk::lang.plugin.description',
            'author'      => 'Ben Thomson',
            'icon'        => 'icon-wrench',
            'homepage'    => 'https://github.com/rainlab/dusk-plugin'
        ];
    }

    public function register()
    {
        if (App::isProduction()) {
            return;
        }

        // Load service provider
        App::register(ServiceProvider::class);
    }
}
