<?php

namespace Core\Adaptors\Fakes;

use LogicException;
use PHPUnit\Framework\Assert;

/**
 * A stand-in for whatever an adaptor wraps (Monolog, Guzzle, Symfony Cache...): it records every method called on it,
 * returns what it was told to, and can check afterwards what happened. Made by an adaptor's fake(), e.g. Logger::fake().
 *
 * Checks use PHPUnit's assertions when PHPUnit is loaded (so they count and fail like any other assertion); outside
 * PHPUnit a failed check throws a LogicException. Nothing here needs PHPUnit to run.
 *
 * @package Core\Adaptors\Fakes
 */
class RecordingFake
{
    /**
     * Every call, in order: ['method' => name, 'arguments' => [...]].
     *
     * @var array<int, array{method: string, arguments: array}>
     */
    private array $calls = [];

    /**
     * @param array<string, mixed> $returns What each method returns, by name: a value, or a callable given the call's
     *  arguments. Methods not named return null.
     */
    public function __construct(private readonly array $returns = [])
    {
    }

    /**
     * Record the call and answer it.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        $this->calls[] = ['method' => $name, 'arguments' => $arguments];
        $answer = $this->returns[$name] ?? null;

        return is_callable($answer) ? $answer(...$arguments) : $answer;
    }

    /**
     * The calls made, all of them or one method's.
     *
     * @param string|null $method
     * @param callable|null $where Keeps a call when it returns true, given the call's arguments.
     *
     * @return array<int, array{method: string, arguments: array}>
     */
    public function calls(?string $method = null, ?callable $where = null): array
    {
        return array_values(array_filter(
            $this->calls,
            fn(array $call) => ($method === null || $call['method'] === $method)
                && ($where === null || $where(...$call['arguments']))
        ));
    }

    /**
     * Check a method was called: at least once, or exactly $times times; only counting calls $where accepts.
     *
     * @param string $method
     * @param callable|null $where
     * @param int|null $times
     *
     * @return static
     */
    public function assertCalled(string $method, ?callable $where = null, ?int $times = null): static
    {
        $count = count($this->calls($method, $where));
        if ($times === null) {
            self::check($count > 0, "Expected $method() to be called; it was not" . $this->callSummary());
        } else {
            self::check($count === $times, "Expected $method() to be called $times times; it was $count" . $this->callSummary());
        }

        return $this;
    }

    /**
     * Check a method was not called (or not in a way $where accepts).
     *
     * @param string $method
     * @param callable|null $where
     *
     * @return static
     */
    public function assertNotCalled(string $method, ?callable $where = null): static
    {
        $count = count($this->calls($method, $where));
        self::check($count === 0, "Expected $method() not to be called; it was called $count times");

        return $this;
    }

    /**
     * Check nothing at all was called.
     *
     * @return static
     */
    public function assertNothingCalled(): static
    {
        self::check($this->calls === [], 'Expected no calls' . $this->callSummary());

        return $this;
    }

    /**
     * The methods called so far, for failure messages.
     *
     * @return string
     */
    private function callSummary(): string
    {
        return $this->calls === []
            ? ' (no calls at all)'
            : ' (calls: ' . implode(', ', array_map(fn(array $call) => $call['method'] . '()', $this->calls)) . ')';
    }

    /**
     * Pass or fail a check: a PHPUnit assertion when PHPUnit is loaded, a LogicException otherwise.
     *
     * @param bool $passed
     * @param string $message
     *
     * @return void
     */
    protected static function check(bool $passed, string $message): void
    {
        if (class_exists(Assert::class)) {
            Assert::assertTrue($passed, $message);
            return;
        }
        if (!$passed) {
            throw new LogicException($message);
        }
    }
}
