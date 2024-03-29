<?php namespace Winter\Dusk\Classes;

use Laravel\Dusk\Page as BasePage;

abstract class BackendPage extends BasePage
{
    /**
     * Get the global element shortcuts for the site.
     *
     * @return array
     */
    public static function siteElements()
    {
        return [
            '@mainMenu' => '#layout-mainmenu',
            '@accountMenu' => '#layout-mainmenu .mainmenu-account > a',

            '@sideNav' => '#layout-sidenav > ul',
            '@sidePanel' => '#layout-side-panel',
            '@sidePanelFixButton' => '#layout-side-panel a.fix-button',
        ];
    }
}
