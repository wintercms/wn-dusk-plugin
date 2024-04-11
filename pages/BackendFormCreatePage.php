<?php

namespace Winter\Dusk\Pages;

use Laravel\Dusk\Browser;

class BackendFormCreatePage extends BackendPage
{
    /**
     * The form config being tested.
     */
    protected string $formConfig;

    /**
     * The pass-through file name for this request.
     */
    protected string $passthroughFile;

    public function __construct(string $formConfig)
    {
        $this->formConfig = $formConfig;

        try {
            if (!is_dir(base_path('storage/dusk/form-tester'))) {
                mkdir(base_path('storage/dusk/form-tester'));
            }

            do {
                $this->passthroughFile = uniqid('', true);
            } while (file_exists(base_path('storage/dusk/form-tester/' . $this->passthroughFile)));

            file_put_contents(
                base_path('storage/dusk/form-tester/' . $this->passthroughFile),
                serialize([
                    'formConfig' => $formConfig
                ])
            );
        } catch (\Throwable $ex) {
            throw new \Exception('Unable to pass through form config to the browser. Please check your permissions');
        }
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/backend/winter/dusk/formtester/create/' . $this->passthroughFile;
    }

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

            '@formContainer' => '#Form',
            '@primaryTabs' => '#Form-primaryTabs ul.nav.nav-tabs',
        ];
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser
            ->assertPresent('@mainMenu')
            ->assertPresent('@accountMenu')
            ->assertPresent('@formContainer');
    }
}
