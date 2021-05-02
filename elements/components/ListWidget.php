<?php namespace Winter\Dusk\Elements\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class ListWidget extends BaseComponent
{
    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return 'div[data-control="listwidget"]';
    }

    /**
     * Assert that the browser page contains the component.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertVisible($this->selector());
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@data' => 'table.table.data',
            '@data-columns' => 'table.table.data > thead > tr:first-child > th',
            '@data-rows' => 'table.table.data > tbody > tr',
        ];
    }

    /**
     * Asserts the count of rows in a List widget.
     *
     * @param Browser $browser The instance of the Browser.
     * @param int $count The expected number of rows.
     * @param string $message A custom message to throw if the assertion fails.
     * @return void
     */
    public function assertCount(Browser $browser, $count, $message = null)
    {
        $rows = 0;

        $browser->within($this, function (Browser $browser) use (&$rows) {
            $rows = count($browser->elements('@data-rows'));
        });

        if (empty($message)) {
            $message = 'Expected ' . $count . ' rows in List widget, found ' . $rows;
        }

        \PHPUnit\Framework\Assert::assertEquals($count, $rows, $message);
    }

    /**
     * Hovers over a specified row.
     */
}
