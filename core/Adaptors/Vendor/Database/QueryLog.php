<?php

namespace Core\Adaptors\Vendor\Database;

use Core\Adaptors\Fakes\RecordingFake;
use Core\Adaptors\Vendor\Logger\Logger;

/**
 * The log driver (DB_CONNECTION=log, or testing mode): stands in for the database connection, records every statement
 * and writes it to the log, and runs nothing. Reads come back empty, writes report 0 rows. It replaced the old
 * testing flag inside DbConnect::exec().
 *
 * @package Core\Adaptors\Vendor\Database
 */
class QueryLog extends RecordingFake
{
    /**
     * The methods that carry SQL as their first argument.
     */
    private const STATEMENT_METHODS = ['exec', 'query', 'prepare'];

    public function __construct()
    {
        $emptyResult = fn() => new RecordingFake([
            'execute' => true,
            'fetch' => false,
            'fetchAll' => [],
            'fetchColumn' => false,
            'fetchObject' => false,
            'rowCount' => 0,
        ]);
        parent::__construct([
            'exec' => 0,
            'query' => $emptyResult,
            'prepare' => $emptyResult,
            'lastInsertId' => '0',
            'beginTransaction' => true,
            'commit' => true,
            'rollBack' => true,
            'inTransaction' => false,
        ]);
    }

    /**
     * Record the call, log it when it carries SQL, and answer as the empty connection would.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (in_array($name, self::STATEMENT_METHODS, true)) {
            Logger::info('Not run (log driver): ' . ($arguments[0] ?? ''));
        }

        return parent::__call($name, $arguments);
    }

    /**
     * The SQL of every statement sent so far, in order.
     *
     * @return string[]
     */
    public function statements(): array
    {
        return array_map(
            fn(array $call) => (string)($call['arguments'][0] ?? ''),
            array_values(array_filter(
                $this->calls(),
                fn(array $call) => in_array($call['method'], self::STATEMENT_METHODS, true)
            ))
        );
    }
}
