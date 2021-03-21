<?php namespace Winter\Dusk\Classes;

use Config;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as DuskTestCase;

abstract class BrowserTestCase extends DuskTestCase
{
    use \Winter\Dusk\Concerns\CreatesApplication;
    use \Winter\Dusk\Concerns\RunsMigrations;
    use \Winter\Dusk\Concerns\TestsPlugins;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }

    public function setUp(): void
    {
        $this->resetManagers();

        parent::setUp();

        // Ensure system is up to date
        if ($this->usingTestDatabase) {
            $this->runWinterUpCommand();
        }

        // Detect a plugin and autoload it, if necessary
        $this->detectPlugin();

        // Disable mailer
        \Mail::pretend();

        $screenshotDir = Config::get('winter.dusk::dusk.screenshotsPath', storage_path('dusk/screenshots'));
        $consoleDir = Config::get('winter.dusk::dusk.consolePath', storage_path('dusk/console'));
        if (!is_dir($screenshotDir)) {
            mkdir($screenshotDir, 0777, true);
        }
        if (!is_dir($consoleDir)) {
            mkdir($consoleDir, 0777, true);
        }

        Browser::$baseUrl = $this->baseUrl();
        Browser::$storeScreenshotsAt = $screenshotDir;
        Browser::$storeConsoleLogAt = $consoleDir;
        Browser::$userResolver = function () {
            return $this->user();
        };

        $this->setupMacros();
    }

    public function tearDown(): void
    {
        if ($this->usingTestDatabase && isset($this->testDatabasePath)) {
            unlink($this->testDatabasePath);
        }

        parent::tearDown();
    }

    /**
     * Defines Winter macros for use in browser tests
     *
     * @return void
     */
    protected function setupMacros()
    {
        Browser::macro('hasClass', function (string $selector, string $class) {
            $classes = preg_split('/\s+/', $this->attribute($selector, 'class'), -1, PREG_SPLIT_NO_EMPTY);

            if (empty($classes)) {
                return false;
            }

            return in_array($class, $classes);
        });
    }

    /**
     * Return the default user to authenticate.
     *
     * @return \Backend\Models\User
     */
    protected function user()
    {
        return \Backend\Models\User::where('login', 'admin')->first();
    }
}
