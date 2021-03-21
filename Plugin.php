<?php namespace Winter\Dusk;

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
            'name'        => 'winter.dusk::lang.plugin.name',
            'description' => 'winter.dusk::lang.plugin.description',
            'author'      => 'Winter CMS',
            'icon'        => 'icon-wrench',
            'homepage'    => 'https://github.com/winter/wn-dusk-plugin'
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
