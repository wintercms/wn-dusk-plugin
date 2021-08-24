<?php namespace Winter\Dusk\Tests\Browser\Backend\Cms;

use Laravel\Dusk\Browser;
use Winter\Dusk\Classes\BrowserTestCase;
use Winter\Dusk\Tests\Pages\Backend\Cms;

class TemplateTest extends BrowserTestCase
{
    public function testPageTemplates()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->login()
                ->visit(new Cms)
                ->pause(200)
                ->screenshot($this->testScreenshot('1. View CMS Pages'));

            // Fix side panel, if necessary
            if ($browser->hasClass('', 'side-panel-not-fixed')) {
                $browser
                    ->mouseover('@sideNav > li[data-menu-item="pages"]')
                    ->waitFor('@sidePanel')
                    ->mouseover('@sidePanel')
                    ->waitFor('@sidePanelFixButton')
                    ->click('@sidePanelFixButton')
                    ->screenshot($this->testScreenshot('1a. Fixate Side Panel'));
            }

            // Add a new page
            $browser
                ->click('form[data-template-type="page"] button[data-control="create-template"]')
                ->waitFor('#cms-master-tabs .tab-content .tab-pane')
                ->screenshot($this->testScreenshot('2. Add a new Page'));

            $tabId = $browser->attribute('#cms-master-tabs .tab-content .tab-pane', 'id');

            $browser->assertPresent('a[data-toggle="tab"][data-target="#' . $tabId . '"]');
            $this->assertEquals('New page', $browser->text('a[data-toggle="tab"][data-target="#' . $tabId . '"]'));

            $browser
                ->type('input[name="settings[title]"]', 'Functional Test Page')
                ->pause(100)
                ->screenshot($this->testScreenshot('3. Input title'))

                // Check that slug values are working
                ->assertInputValue('input[name="settings[url]"]', '/functional-test-page')
                ->assertInputValue('input[name="fileName"]', 'functional-test-page')

                ->clear('input[name="settings[url]"]')
                ->type('input[name="settings[url]"]', '/xxx/functional/test/page')
                ->clear('input[name="fileName"]')
                ->type('input[name="fileName"]', 'xxx_functional_test_page.htm')
                ->screenshot($this->testScreenshot('4. Modify settings'))

                // Check that slug values have not been re-added after manual entry
                ->assertInputValue('input[name="settings[url]"]', '/xxx/functional/test/page')
                ->assertInputValue('input[name="fileName"]', 'xxx_functional_test_page.htm');

            // Save the new page
            $browser
                ->click('a[data-request="onSave"]')
                ->waitFor('.flash-message')
                ->screenshot($this->testScreenshot('5. Save new page'))
                ->assertSeeIn('.flash-message', 'Template saved.');

            $this->assertEquals(
                'Functional Test Page',
                $browser->attribute('a[data-toggle="tab"][data-target="#' . $tabId . '"] span.title', 'title')
            );

            // Close the tab
            $browser
                ->click('li[data-tab-id^="page-"][data-tab-id$="-xxx_functional_test_page.htm"] span.tab-close')
                ->pause(100)
                ->screenshot($this->testScreenshot('6. Close tab'))
                ->assertMissing('#cms-master-tabs .tab-content .tab-pane');

            // Re-open the page
            $browser
                ->click('div#TemplateList-pageList-template-list li[data-item-path="xxx_functional_test_page.htm"] a')
                ->waitFor('#cms-master-tabs .tab-content .tab-pane')
                ->screenshot($this->testScreenshot('7. Re-open page'))

                // Check that saved details are still there
                ->assertInputValue('input[name="settings[title]"]', 'Functional Test Page')
                ->assertInputValue('input[name="settings[url]"]', '/xxx/functional/test/page')
                ->assertInputValue('input[name="fileName"]', 'xxx_functional_test_page.htm');

            // Delete the page
            $browser
                ->click('button[data-request="onDelete"]')
                ->waitFor('.sweet-alert.showSweetAlert.visible')
                ->pause(300)
                ->screenshot($this->testScreenshot('8. Delete page'))
                ->click('.sweet-alert.showSweetAlert.visible button.confirm')
                ->waitUntilMissing('div#TemplateList-pageList-template-list li[data-item-path="xxx_functional_test_page.htm"]')
                ->screenshot($this->testScreenshot('9. Page deleted'));
        });
    }

    public function testPartialTemplates()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->login()
                ->visit(new Cms)
                ->pause(200);

            // Fix side panel, if necessary
            if ($browser->hasClass('', 'side-panel-not-fixed')) {
                $browser
                    ->mouseover('@sideNav > li[data-menu-item="pages"]')
                    ->waitFor('@sidePanel')
                    ->mouseover('@sidePanel')
                    ->waitFor('@sidePanelFixButton')
                    ->click('@sidePanelFixButton');
            }

            $browser
                ->click('@sideNav > li[data-menu-item="partials"] a')
                ->screenshot($this->testScreenshot('1. View CMS Partials'));

            // Add a new partial
            $browser
                ->click('form[data-template-type="partial"] button[data-control="create-template"]')
                ->waitFor('#cms-master-tabs .tab-content .tab-pane')
                ->screenshot($this->testScreenshot('2. Add a new partial'));

            $tabId = $browser->attribute('#cms-master-tabs .tab-content .tab-pane', 'id');

            $browser->assertPresent('a[data-toggle="tab"][data-target="#' . $tabId . '"]');
            $this->assertEquals('New partial', $browser->text('a[data-toggle="tab"][data-target="#' . $tabId . '"]'));

            $browser
                ->type('input[name="fileName"]', 'xxx_functional_test_partial')
                ->type('input[name="settings[description]"]', 'Test Partial')
                ->screenshot($this->testScreenshot('3. Add details'));

            // Save the new partial
            $browser
                ->click('a[data-request="onSave"]')
                ->waitFor('.flash-message')
                ->screenshot($this->testScreenshot('4. Save partial'))
                ->assertSeeIn('.flash-message', 'Template saved.');

            $this->assertEquals(
                'xxx_functional_test_partial',
                $browser->attribute('a[data-toggle="tab"][data-target="#' . $tabId . '"] span.title', 'title')
            );

            // Close the tab
            $browser
                ->click('li[data-tab-id^="partial-"][data-tab-id$="-xxx_functional_test_partial.htm"] span.tab-close')
                ->pause(100)
                ->screenshot($this->testScreenshot('5. Close tab'))
                ->assertMissing('#cms-master-tabs .tab-content .tab-pane');

            // Re-open the partial
            $browser
                ->click('div#TemplateList-partialList-template-list li[data-item-path="xxx_functional_test_partial.htm"] a')
                ->waitFor('#cms-master-tabs .tab-content .tab-pane')
                ->screenshot($this->testScreenshot('6. Re-open partial'))

                // Check that saved details are still there
                ->assertInputValue('input[name="fileName"]', 'xxx_functional_test_partial.htm')
                ->assertInputValue('input[name="settings[description]"]', 'Test Partial');

            // Delete the partial
            $browser
                ->click('button[data-request="onDelete"]')
                ->waitFor('.sweet-alert.showSweetAlert.visible')
                ->pause(300)
                ->screenshot($this->testScreenshot('7. Delete partial'))
                ->click('.sweet-alert.showSweetAlert.visible button.confirm')
                ->waitUntilMissing('div#TemplateList-partialList-template-list li[data-item-path="xxx_functional_test_partial.htm"]')
                ->screenshot($this->testScreenshot('8. Partial deleted'));
        });
    }

    public function testLayoutTemplates()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->login()
                ->visit(new Cms)
                ->pause(200);

            // Fix side panel, if necessary
            if ($browser->hasClass('', 'side-panel-not-fixed')) {
                $browser
                    ->mouseover('@sideNav > li[data-menu-item="pages"]')
                    ->waitFor('@sidePanel')
                    ->mouseover('@sidePanel')
                    ->waitFor('@sidePanelFixButton')
                    ->click('@sidePanelFixButton');
            }

            $browser
                ->click('@sideNav > li[data-menu-item="layouts"] a')
                ->screenshot($this->testScreenshot('1. View CMS Layouts'));

            // Add a new layout
            $browser
                ->click('form[data-template-type="layout"] button[data-control="create-template"]')
                ->waitFor('#cms-master-tabs .tab-content .tab-pane')
                ->screenshot($this->testScreenshot('2. Add a new layout'));

            $tabId = $browser->attribute('#cms-master-tabs .tab-content .tab-pane', 'id');

            $browser->assertPresent('a[data-toggle="tab"][data-target="#' . $tabId . '"]');
            $this->assertEquals('New layout', $browser->text('a[data-toggle="tab"][data-target="#' . $tabId . '"]'));

            $browser
                ->type('input[name="fileName"]', 'xxx_functional_test_layout')
                ->type('input[name="settings[description]"]', 'Test Layout')
                ->screenshot($this->testScreenshot('3. Add details'));

            // Save the new layout
            $browser
                ->click('a[data-request="onSave"]')
                ->waitFor('.flash-message')
                ->screenshot($this->testScreenshot('4. Save layout'))
                ->assertSeeIn('.flash-message', 'Template saved.');

            $this->assertEquals(
                'xxx_functional_test_layout',
                $browser->attribute('a[data-toggle="tab"][data-target="#' . $tabId . '"] span.title', 'title')
            );

            // Close the tab
            $browser
                ->click('li[data-tab-id^="layout-"][data-tab-id$="-xxx_functional_test_layout.htm"] span.tab-close')
                ->pause(100)
                ->screenshot($this->testScreenshot('5. Close tab'))
                ->assertMissing('#cms-master-tabs .tab-content .tab-pane');

            // Re-open the partial
            $browser
                ->click('div#TemplateList-layoutList-template-list li[data-item-path="xxx_functional_test_layout.htm"] a')
                ->waitFor('#cms-master-tabs .tab-content .tab-pane')
                ->screenshot($this->testScreenshot('6. Re-open layout'))

                // Check that saved details are still there
                ->assertInputValue('input[name="fileName"]', 'xxx_functional_test_layout.htm')
                ->assertInputValue('input[name="settings[description]"]', 'Test Layout');

            // Delete the partial
            $browser
                ->click('button[data-request="onDelete"]')
                ->waitFor('.sweet-alert.showSweetAlert.visible')
                ->pause(300)
                ->screenshot($this->testScreenshot('7. Delete layout'))
                ->click('.sweet-alert.showSweetAlert.visible button.confirm')
                ->waitUntilMissing('div#TemplateList-layoutList-template-list li[data-item-path="xxx_functional_test_layout.htm"]')
                ->screenshot($this->testScreenshot('8. Layout deleted'));
        });
    }

    public function testContentTemplates()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->login()
                ->visit(new Cms)
                ->pause(200);

            // Fix side panel, if necessary
            if ($browser->hasClass('', 'side-panel-not-fixed')) {
                $browser
                    ->mouseover('@sideNav > li[data-menu-item="pages"]')
                    ->waitFor('@sidePanel')
                    ->mouseover('@sidePanel')
                    ->waitFor('@sidePanelFixButton')
                    ->click('@sidePanelFixButton');
            }

            $browser
                ->click('@sideNav > li[data-menu-item="content"] a')
                ->screenshot($this->testScreenshot('1. View CMS Content'));

            // Add a new content file
            $browser
                ->click('form[data-template-type="content"] button[data-control="create-template"]')
                ->waitFor('#cms-master-tabs .tab-content .tab-pane')
                ->screenshot($this->testScreenshot('2. Add new content file'));

            $tabId = $browser->attribute('#cms-master-tabs .tab-content .tab-pane', 'id');

            $browser->assertPresent('a[data-toggle="tab"][data-target="#' . $tabId . '"]');
            $this->assertStringContainsString('content', $browser->text('a[data-toggle="tab"][data-target="#' . $tabId . '"]'));

            $browser
                ->type('input[name="fileName"]', 'xxx_functional_test_content.txt')
                ->screenshot($this->testScreenshot('3. Add details'));

            // Save the new content file
            $browser
                ->click('a[data-request="onSave"]')
                ->waitFor('.flash-message')
                ->screenshot($this->testScreenshot('4. Save content file'))
                ->assertSeeIn('.flash-message', 'Template saved.');

            $this->assertEquals(
                'xxx_functional_test_content.txt',
                $browser->attribute('a[data-toggle="tab"][data-target="#' . $tabId . '"] span.title', 'title')
            );

            // Close the tab
            $browser
                ->click('li[data-tab-id^="content-"][data-tab-id$="-xxx_functional_test_content.txt"] span.tab-close')
                ->pause(100)
                ->screenshot($this->testScreenshot('5. Close tab'))
                ->assertMissing('#cms-master-tabs .tab-content .tab-pane');

            // Re-open the partial
            $browser
                ->click('div#TemplateList-contentList-template-list li[data-item-path="xxx_functional_test_content.txt"] a')
                ->waitFor('#cms-master-tabs .tab-content .tab-pane')
                ->screenshot($this->testScreenshot('6. Re-open content file'))

                // Check that saved details are still there
                ->assertInputValue('input[name="fileName"]', 'xxx_functional_test_content.txt');

            // Delete the partial
            $browser
                ->click('button[data-request="onDelete"]')
                ->waitFor('.sweet-alert.showSweetAlert.visible')
                ->pause(300)
                ->screenshot($this->testScreenshot('7. Delete content file'))
                ->click('.sweet-alert.showSweetAlert.visible button.confirm')
                ->waitUntilMissing('div#TemplateList-contentList-template-list li[data-item-path="xxx_functional_test_content.txt"]')
                ->screenshot($this->testScreenshot('8. Content file deleted'));
        });
    }
}
