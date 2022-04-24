<?php

namespace Tests;

use Core\Utilities\Functional\Pure;
use Faker\Factory;
use Faker\Generator;
use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Class TestCase
 *
 * @package Tests
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * The callbacks that should be run after the application is created.
     *
     * @var array
     */
    public array $afterApplicationCreatedCallbacks = [];

    /**
     * The callbacks that should be run before the application is destroyed.
     *
     * @var array
     */
    public array $beforeApplicationDestroyedCallbacks = [];

    /**
     * Indicates if we have made it through the base setUp function.
     *
     * @var bool
     */
    public bool $setUpHasRun = false;

    /**
     * The Faker instance.
     *
     * @var Generator
     */
    public Generator $faker;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->setUpFaker();

        Pure::extractAll();

        foreach ($this->afterApplicationCreatedCallbacks as $callback) {
            call_user_func($callback);
        }

        $this->setUpHasRun = true;
    }

    /**
     * Setup up the Faker instance.
     *
     * @return void
     */
    public function setUpFaker(): void
    {
        $this->faker = $this->makeFaker();
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return bool|mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        return call_user_func($this->{$name}, ...$arguments);
    }

    /**
     * Get the default Faker instance for a given locale.
     *
     * @param string|null $locale
     *
     * @return Generator
     */
    public function faker(string $locale = null): Generator
    {
        return is_null($locale) ? $this->faker : $this->makeFaker($locale);
    }

    /**
     * Create a Faker instance for the given locale.
     *
     * @param string|null $locale
     *
     * @return Generator
     */
    public function makeFaker(string $locale = null): Generator
    {
        return Factory::create($locale ?? Factory::DEFAULT_LOCALE);
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->setUpHasRun = false;

        if (property_exists($this, 'serverVariables')) {
            $this->serverVariables = [];
        }

        if (property_exists($this, 'defaultHeaders')) {
            $this->defaultHeaders = [];
        }

        if (class_exists('Mockery')) {
            Mockery::close();
        }

        $this->afterApplicationCreatedCallbacks = [];
        $this->beforeApplicationDestroyedCallbacks = [];
    }

    /**
     * Register a callback to be run after the application is created.
     *
     * @param callable $callback
     *
     * @return void
     */
    public function afterApplicationCreated(callable $callback): void
    {
        $this->afterApplicationCreatedCallbacks[] = $callback;

        if ($this->setUpHasRun) {
            call_user_func($callback);
        }
    }

    /**
     * Register a callback to be run before the application is destroyed.
     *
     * @param callable $callback
     *
     * @return void
     */
    public function beforeApplicationDestroyed(callable $callback): void
    {
        $this->beforeApplicationDestroyedCallbacks[] = $callback;
    }
}
