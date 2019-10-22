<?php

namespace Core\Tests;

use Core\Utilities\Functional\PureTrait;
use Faker\Factory;
use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Class TestCase
 *
 * @package Core\Tests
 *
 * @method callable pipe(callable[] ...$fns)
 * @method callable curry(string | callable $fn, string | object $class = __CLASS__)
 * @method callable trace(string $label = '')
 * @method callable tt(string $label = '')
 */
abstract class TestCase extends BaseTestCase
{
    use PureTrait;

    /**
     * The callbacks that should be run after the application is created.
     *
     * @var array
     */
    protected $afterApplicationCreatedCallbacks = [];

    /**
     * The callbacks that should be run before the application is destroyed.
     *
     * @var array
     */
    protected $beforeApplicationDestroyedCallbacks = [];

    /**
     * Indicates if we have made it through the base setUp function.
     *
     * @var bool
     */
    protected $setUpHasRun = false;

    /**
     * The Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->setUpFaker();

        foreach ($this->afterApplicationCreatedCallbacks as $callback) {
            call_user_func($callback);
        }

        $this->setUpPureFunctions();

        $this->setUpHasRun = true;
    }

    /**
     * Setup up the Faker instance.
     *
     * @return void
     */
    protected function setUpFaker()
    {
        $this->faker = $this->makeFaker();
    }

    /**
     *
     */
    protected function setUpPureFunctions()
    {
        foreach ($this->importFunctions() as $name => $callable) {
            $this->$name = $callable;
        }
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return bool|mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func($this->{$name}, ...$arguments);
    }

    /**
     * Get the default Faker instance for a given locale.
     *
     * @param  string $locale
     *
     * @return \Faker\Generator
     */
    protected function faker($locale = null)
    {
        return is_null($locale) ? $this->faker : $this->makeFaker($locale);
    }

    /**
     * Create a Faker instance for the given locale.
     *
     * @param  string $locale
     *
     * @return \Faker\Generator
     */
    protected function makeFaker($locale = null)
    {
        return Factory::create($locale ?? Factory::DEFAULT_LOCALE);
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown()
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
     * @param  callable $callback
     *
     * @return void
     */
    public function afterApplicationCreated(callable $callback)
    {
        $this->afterApplicationCreatedCallbacks[] = $callback;

        if ($this->setUpHasRun) {
            call_user_func($callback);
        }
    }

    /**
     * Register a callback to be run before the application is destroyed.
     *
     * @param  callable $callback
     *
     * @return void
     */
    protected function beforeApplicationDestroyed(callable $callback)
    {
        $this->beforeApplicationDestroyedCallbacks[] = $callback;
    }
}
