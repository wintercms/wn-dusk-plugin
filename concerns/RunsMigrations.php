<?php

namespace Winter\Dusk\Concerns;

use Illuminate\Support\Facades\Artisan;

trait RunsMigrations
{
    protected function runWinterUpCommand()
    {
        Artisan::call('winter:up');
    }

    protected function runWinterDownCommand()
    {
        Artisan::call('winter:down --force');
    }
}
