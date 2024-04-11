<?php

namespace Winter\Dusk\Classes;

use Laravel\Dusk\Browser;

class BackendFormTestCase extends BrowserTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setupMacros()
    {
        parent::setupMacros();

        Browser::macro('openTab', function (string $tabName) {
            $this->with('@primaryTabs', function (Browser $tabs) use ($tabName) {
                $tabs->withinEach('li', function (Browser $tab) use ($tabName) {
                    if ($tab->text('a') === $tabName) {
                        $tab->click('a');
                    }
                });
            });

            return $this;
        });
    }
}
