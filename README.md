# Dusk Plugin

Integrates Laravel Dusk browser testing into Winter CMS, providing Winter CMS and plugin developers with the tools to
run automated tests on a fully functional Winter CMS instance through a virtual browser.

> **Note:** This plugin is intended to be used for development purposes only. Configured improperly, it can allow users
> to circumvent authentication and sign in as any user. This plugin should be specified as a **development dependency** (ie. `require-dev`) only.

## Getting started

To install the plugin, you may install it through the [Winter CMS Marketplace](https://wintercms.com/plugin/winter-dusk), or you may install it using Composer:

```bash
composer require --dev winter/dusk-plugin
```

Then, run the migrations to ensure the plugin is enabled:

```bash
php artisan winter:up
```

To run the browser tests, you must install the Chrome web-driver and have the Google Chrome browser installed on the machine running the tests. The web-driver can be installed by running the following command:

```bash
php artisan dusk:chrome-driver
```

## Running the tests

By default, the browser tests are configured to run the tests against a website served by the in-built Laravel web server. You may start this server by running the following:

```bash
php artisan serve
```

To start the browser tests, run:

```bash
php artisan dusk
```

This will execute all available browser tests in all enabled plugins on your Winter CMS installation. If you would like to run the tests for one plugin only, you may add the plugin code as an argument:

```bash
php artisan dusk Acme.Blog
```

As a shortcut after running the tests, you may re-run the failed tests by executing the following command:

```bash
php artisan dusk:fails
```

---

## Creating browser tests for your plugin

The Dusk plugin makes it a breeze to create browser tests for your own plugin.

Browser test classes should reside in the **tests/browser** folder of your plugin. Each test class file should ended with `Test.php` to indicate it is a class of test cases, and should extend the `Winter\Dusk\Classes\BrowserTestCase` class.

For example, a blog plugin may wish to create a **BlogTest.php** file with the following content:

```php
<?php namespace Acme\Blog\Tests\Browser;

use Winter\Dusk\Classes\BrowserTestCase;

class BlogTest extends BrowserTestCase
{
    public function testPost()
    {
        // the test to run
    }
}
```

Each test method in the class should be prefixed with **test** to denote that it is a test case.

To run the browser tests for your plugin, simply run the following:

```bash
php artisan dusk Acme.Blog
```
