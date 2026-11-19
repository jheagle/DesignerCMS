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
        self::check(
            $this->matching($level, $contains) !== [],
            "Expected a line logged at $level" . ($contains === '' ? '' : " containing \"$contains\"") . $this->loggedSummary()
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
        self::check(
            $this->matching($level, $contains) === [],
            "Expected no line logged at $level" . ($contains === '' ? '' : " containing \"$contains\"") . $this->loggedSummary()
        );

        return $this;
    }

    /**
     * The calls at a level whose message contains $contains (any message when empty).
     *
     * @param string $level
     * @param string $contains
     *
     * @return array
     */
    private function matching(string $level, string $contains): array
    {
        return $this->calls(
            $level,
            fn(mixed $message = '') => $contains === '' || str_contains((string)$message, $contains)
        );
    }

    /**
     * What was logged, for failure messages: '; nothing was logged' or '; logged: error: ... | info: ...'.
     *
     * @return string
     */
    private function loggedSummary(): string
    {
        $lines = array_map(
            fn(array $call) => $call['method'] . ': ' . (string)($call['arguments'][0] ?? ''),
            $this->calls()
        );

        return $lines === [] ? '; nothing was logged' : '; logged: ' . implode(' | ', $lines);
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
