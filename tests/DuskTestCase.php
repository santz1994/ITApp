<?php

namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        if (! static::runningInSail()) {
            // If a local ChromeDriver is already listening, proceed.
            $host = parse_url($_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515', PHP_URL_HOST) ?: '127.0.0.1';
            $port = parse_url($_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515', PHP_URL_PORT) ?: 9515;
            $reachable = false;
            try {
                $fp = @fsockopen($host, $port, $errno, $errstr, 1);
                if ($fp) {
                    fclose($fp);
                    $reachable = true;
                }
            } catch (\Throwable $_) {
                $reachable = false;
            }

            if (! $reachable) {
                // Try to start ChromeDriver (Laravel Dusk helper). If it fails,
                // fall back to skipping Dusk tests so the test run doesn't error.
                try {
                    static::startChromeDriver();
                    // give the driver a moment to start
                    sleep(1);
                    $fp2 = @fsockopen($host, $port, $errno, $errstr, 1);
                    if ($fp2) {
                        fclose($fp2);
                        $reachable = true;
                    }
                } catch (\Throwable $_) {
                    // ignore - we'll skip below
                }
            }

            if (! $reachable) {
                // Skip Dusk tests cleanly when ChromeDriver is not available.
                throw new \PHPUnit\Framework\SkippedTestError('ChromeDriver not available on ' . ($host . ':' . $port) . ' - skipping Dusk tests.');
            }
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments(collect([
            '--window-size=1920,1080',
            '--disable-gpu',
            '--headless',
            '--disable-dev-shm-usage',
            '--no-sandbox',
        ])->unless($this->hasHeadlessDisabled(), function ($items) {
            return $items->reject(function ($item) {
                return $item === '--headless';
            });
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     *
     * @return bool
     */
    protected function hasHeadlessDisabled(): bool
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) ||
               isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    /**
     * Determine if the tests are running within Laravel Sail.
     *
     * @return bool
     */
    protected static function runningInSail(): bool
    {
        return env('LARAVEL_SAIL') === '1';
    }
}
