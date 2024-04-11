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
                ->screenshot($this->testScreenshot('3. Fill field A'));

            $browser
                ->waitFor('[name="TestModel[field_b]"]', 2)
                ->screenshot($this->testScreenshot('4. Field B shown'));

            $browser
                ->type('TestModel[field_b]', 'test')
                ->keys('[name="TestModel[field_b]"]', '{tab}')
                ->screenshot($this->testScreenshot('5. Fill field B'));

            $browser
                ->waitFor('[name="TestModel[field_c]"]', 2)
                ->screenshot($this->testScreenshot('6. Field C shown'));
        });
    }
}
