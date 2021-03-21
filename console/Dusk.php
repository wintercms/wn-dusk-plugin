<?php namespace Winter\Dusk\Console;

use Config;
use Laravel\Dusk\Console\DuskCommand as BaseDuskCommand;
use Winter\Storm\Exception\ApplicationException;
use System\Classes\PluginManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class Dusk extends BaseDuskCommand
{
    /**
     * @var string The name and signature of the console command.
     */
    protected $signature = 'dusk {plugin?} {--without-tty : Disable output to TTY}';

    /**
     * @var string The console command description.
     */
    protected $description = 'Run the Dusk tests for the entire application, or for a single Winter plugin.';

    /**
     * @var array Selected plugins, and the path to their browser tests.
     */
    protected $plugins = [];

    /**
     * @var bool Is the Dusk configuration directory stubbed?
     */
    protected $stubbedConfigDir = false;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->collatePlugins();
        $this->purgeScreenshots();
        $this->purgeConsoleLogs();

        return $this->withDuskEnvironment(function () {
            $process = (new Process(array_merge(
                $this->binary(), $this->phpunitArguments($this->getPHPUnitArguments())
            )))->setTimeout(null);

            try {
                $process->setTty(! $this->option('without-tty'));
            } catch (RuntimeException $e) {
                $this->output->writeln('Warning: '.$e->getMessage());
            }

            try {
                return $process->run(function ($type, $line) {
                    $this->output->write($line);
                });
            } catch (ProcessSignaledException $e) {
                if (extension_loaded('pcntl') && $e->getSignal() !== SIGINT) {
                    throw $e;
                }
            }
        });
    }

    /**
     * Collate plugins to run browser tests for.
     *
     * @return void
     */
    protected function collatePlugins()
    {
        $pluginManager = PluginManager::instance();

        if ($selectedPlugin = $this->argument('plugin')) {
            if (!$pluginManager->exists($selectedPlugin)) {
                throw new ApplicationException('Plugin "' . $selectedPlugin . '" is not installed or enabled.');
            }

            $this->plugins[$selectedPlugin] = str_replace(base_path(), '.', $pluginManager->getPluginPath($selectedPlugin));
        } else {
            foreach (array_keys($pluginManager->getPlugins()) as $plugin) {
                $this->plugins[$plugin] = str_replace(base_path(), '.', $pluginManager->getPluginPath($plugin));
            }
        }

        if (!count($this->plugins)) {
            throw new ApplicationException('No tests to run.');
        }
    }

    /**
     * Setup the Dusk environment.
     *
     * @return void
     */
    protected function setupDuskEnvironment()
    {
        if (file_exists($this->duskFile())) {
            if (!file_exists(base_path('.env'))) {
                $this->stubEnvironment();
            } elseif (file_get_contents(base_path('.env')) !== file_get_contents($this->duskFile())) {
                $this->backupEnvironment();
            }
            if (!is_dir(base_path('config/dusk'))) {
                $this->stubConfigDir();
                $this->stubbedConfigDir = true;
            }
            $this->refreshEnvironment();
        }

        $this->writeConfiguration();
        $this->setupSignalHandler();
    }

    /**
     * Restore the original environment.
     *
     * @return void
     */
    protected function teardownDuskEnviroment()
    {
        $this->removeConfiguration();
        $this->removeConfigDir();

        if (
            file_exists($this->duskFile())
            && (file_exists(base_path('.env.backup')) || file_exists(base_path('.env.blank')))
        ) {
            $this->restoreEnvironment();
        }
    }

    /**
     * Stub a current environment file.
     *
     * @return void
     */
    protected function stubEnvironment()
    {
        touch(base_path('.env.blank'));
        copy($this->duskFile(), base_path('.env'));
    }

    /**
     * Stub configuration directory and files for Dusk.
     *
     * Emulates running the `winter:env` console command.
     *
     * @return void
     */
    protected function stubConfigDir()
    {
        mkdir(base_path('config/dusk'), 0755, true);

        foreach (glob(plugins_path('winter/dusk/stubs/config/dusk/*.php')) as $file) {
            $path = pathinfo($file);
            copy($file, base_path('config/dusk/' . $path['basename']));
        }
    }

    /**
     * Backup the current environment file.
     *
     * @return void
     */
    protected function backupEnvironment()
    {
        copy(base_path('.env'), base_path('.env.backup'));
        copy($this->duskFile(), base_path('.env'));
    }

    /**
     * Restore the backed-up environment file.
     *
     * @return void
     */
    protected function restoreEnvironment()
    {
        if (file_exists(base_path('.env.blank'))) {
            unlink(base_path('.env'));
            unlink(base_path('.env.blank'));
        } else {
            copy(base_path('.env.backup'), base_path('.env'));
            unlink(base_path('.env.backup'));
        }
    }

    /**
     * Write the Dusk PHPUnit configuration.
     *
     * @return void
     */
    protected function writeConfiguration()
    {
        if (
            !file_exists($file = base_path('phpunit.dusk.xml'))
        ) {
            copy($this->duskPhpUnitXmlFile(), $file);
            $this->injectPluginSuites($file);
            return;
        }

        $this->hasPhpUnitConfiguration = true;
    }

    /**
     * Injects test suites for all applicable plugins being tested.
     *
     * @return void
     */
    protected function injectPluginSuites($file)
    {
        $testSuites = [];
        foreach ($this->plugins as $plugin => $path) {
            $testSuites[] = '<testsuite name="' . $plugin . ' Browser Test Suite">'
                . "\n" . '            <directory suffix="Test.php">' . $path . '/tests/browser</directory>'
                . "\n" . '        </testsuite>';
        }

        $contents = file_get_contents($file);
        $contents = str_replace('{testsuites}', implode("\n" . '        ', $testSuites), $contents);
        file_put_contents($file, $contents);
    }

    /**
     * Remove the Dusk PHPUnit configuration.
     *
     * @return void
     */
    protected function removeConfiguration()
    {
        if (! $this->hasPhpUnitConfiguration && file_exists($file = base_path('phpunit.dusk.xml'))) {
            unlink($file);
        }
    }

    /**
     * Remove the stubbed Dusk configuration directory if needed.
     *
     * @return void
     */
    protected function removeConfigDir()
    {
        if (!$this->stubbedConfigDir || !is_dir(base_path('config/dusk'))) {
            return;
        }

        foreach (glob(base_path('config/dusk/*.php')) as $file) {
            unlink($file);
        }

        rmdir(base_path('config/dusk'));
    }

    /**
     * Purge the failure screenshots.
     *
     * @return void
     */
    protected function purgeScreenshots()
    {
        $path = Config::get('winter.dusk::dusk.screenshotsPath', storage_path('dusk/screenshots'));

        if (!is_dir($path)) {
            return;
        }

        $files = Finder::create()
            ->files()
            ->in($path)
            ->name('failure-*');

        foreach ($files as $file) {
            @unlink($file->getRealPath());
        }
    }

    /**
     * Purge the console logs.
     *
     * @return void
     */
    protected function purgeConsoleLogs()
    {
        $path = Config::get('winter.dusk::dusk.consolePath', storage_path('dusk/console'));

        if (!is_dir($path)) {
            return;
        }

        $files = Finder::create()
            ->files()
            ->in($path)
            ->name('*.log');

        foreach ($files as $file) {
            @unlink($file->getRealPath());
        }
    }

    /**
     * Get the name of the Dusk file for the environment.
     *
     * @return string
     */
    protected function duskPhpUnitXmlFile()
    {
        return plugins_path('winter/dusk/stubs/.phpunit.dusk.xml.stub');
    }

    /**
     * Get the name of the Dusk file for the environment.
     *
     * @return string
     */
    protected function duskFile()
    {
        if (file_exists(base_path($file = '.env.dusk.'.$this->laravel->environment()))) {
            return $file;
        }

        return plugins_path('winter/dusk/stubs/.env.dusk.stub');
    }

    /**
     * Strips out this commands arguments and options in order to return arguments/options for PHPUnit.
     *
     * @return array
     */
    protected function getPHPUnitArguments()
    {
        $arguments = $_SERVER['argv'];

        // First two are always "artisan" and "dusk"
        $arguments = array_slice($arguments, 2);

        // Strip plugin argument
        if ($this->argument('plugin')) {
            array_shift($arguments);
        }

        // Strip "--without-tty"
        if ($this->option('without-tty')) {
            array_shift($arguments);
        }

        return $arguments;
    }
}
