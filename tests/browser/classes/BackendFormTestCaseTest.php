<?php

namespace Winter\Dusk\Tests\Browser\FormTester;

use Laravel\Dusk\Browser;
use Winter\Dusk\Classes\BackendFormTestCase;
use Winter\Dusk\Pages\BackendFormCreatePage;

class BackendFormTestCaseTest extends BackendFormTestCase
{
    public function testLoadACreateForm()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->login()
                ->visit(new BackendFormCreatePage('~/plugins/winter/dusk/tests/fixtures/formtester/config_form.yml'))
                ->assertTitleContains('New Form Tester')
                ->screenshot($this->testScreenshot('1. Initial form'));

            $browser
                ->openTab('Dependency test')
                ->screenshot($this->testScreenshot('2. Switch tab'));

            $browser
                ->type('TestModel[field_a]', 'test')
                ->keys('[name="TestModel[field_a]"]', '{tab}')
                ->pause(1000)
                ->screenshot($this->testScreenshot('3. Fill field A'));
        });
    }
}
