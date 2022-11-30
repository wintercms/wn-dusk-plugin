<?php namespace Winter\Dusk\Classes;

use Config;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as DuskTestCase;
use System\Classes\PluginManager;

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

            // Ensure the default user has the specified username and password
            $user = $this->user();
            $user->login = env('DUSK_ADMIN_USER', 'admin');
            $user->password = $user->password_confirmation = env('DUSK_ADMIN_PASS', 'admin');
            $user->save();
        }

        // Detect a plugin and autoload it, if necessary
        $this->detectPlugin();

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

        Browser::macro('assertHasClass', function (string $selector, string $class, string $message = '') {
            \PHPUnit\Framework\Assert::assertTrue(
                $this->hasClass($selector, $class),
                $message ?: 'did not see expected class "' . $class . '" for selector "' . $selector . '"'
            );

            return $this;
        });

        Browser::macro('assertNotHasClass', function (string $selector, string $class, string $message = '') {
            \PHPUnit\Framework\Assert::assertFalse(
                $this->hasClass($selector, $class),
                $message ?: 'saw unexpected class "' . $class . '" for selector "' . $selector . '"'
            );

            return $this;
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

    /**
     * Helper method to generate a screenshot with a contextual path.
     *
     * The path will be returned as "Plugin-Name\TestClass\testMethod\screenshot-name.png".
     *
     * @param string $screenshot
     * @return string
     */
    protected function testScreenshot(string $screenshot)
    {
        // Find test method called
        $method = null;
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        // Ignore this method call in backtrace
        array_shift($backtrace);

        foreach ($backtrace as $step) {
            if (preg_match('/^test/i', $step['function'])) {
                $method = $step;
                break;
            }
        }

        // If no applicable test method can be found, just return the screenshot name so a normal screenshot
        // can be taken.
        if (is_null($method)) {
            return $screenshot;
        }

        $manager = PluginManager::instance();
        $plugin = $manager->getIdentifier($manager->getNamespace($method['class']));
        $class = last(explode('\\', $method['class']));
        $method = $method['function'];

        return implode(DIRECTORY_SEPARATOR, [
            str_replace('.', '-', $plugin),
            $class,
            $method,
            str_slug($screenshot)
        ]);
    }
}
