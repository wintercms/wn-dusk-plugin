<?php namespace RainLab\Dusk\Concerns;

trait RunsMigrations
{
    protected function runOctoberUpCommand()
    {
        \Artisan::call('october:up');
    }

    protected function runOctoberDownCommand()
    {
        \Artisan::call('october:down --force');
    }
}
