<?php

namespace Core\Adaptors\Fakes;

/**
 * Logger::fake()'s stand-in for Monolog: records each log call, with checks by level and message.
 *
 * @package Core\Adaptors\Fakes
 */
class LoggerFake extends RecordingFake
{
    /**
     * Check a message was logged at a level, its text containing $contains (any message when empty).
     *
     * @param string $level e.g. 'error', 'info' (Logger::LEVEL_*).
     * @param string $contains
     *
     * @return static
     */
    public function assertLogged(string $level, string $contains = ''): static
    {
        $logged = $this->calls($level, fn(string $message) => $contains === '' || str_contains($message, $contains));
        $seen = array_map(fn(array $call) => "{$call['method']}: {$call['arguments'][0]}", $this->calls());
        self::check(
            $logged !== [],
            "Expected a line logged at $level" . ($contains === '' ? '' : " containing \"$contains\"")
            . ($seen === [] ? '; nothing was logged' : '; logged: ' . implode(' | ', $seen))
        );

        return $this;
    }

    /**
     * Check nothing was logged at a level containing $contains.
     *
     * @param string $level
     * @param string $contains
     *
     * @return static
     */
    public function assertNotLogged(string $level, string $contains = ''): static
    {
        return $this->assertNotCalled(
            $level,
            fn(string $message) => $contains === '' || str_contains($message, $contains)
        );
    }

    /**
     * Check nothing was logged at all.
     *
     * @return static
     */
    public function assertNothingLogged(): static
    {
        return $this->assertNothingCalled();
    }
}
