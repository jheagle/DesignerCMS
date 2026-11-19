<?php

namespace Core\Adaptors\Vendor\Database;

use SensitiveParameter;

/**
 * What DbConnect needs from a database connection.
 *
 * @package Core\Adaptors\Vendor\Database
 */
interface ConnectionInterface
{
    /**
     * Open the connection; the credentials are not kept.
     *
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $options
     *
     * @return static
     */
    public function connect(
        string $dsn,
        string $username,
        #[SensitiveParameter] string $password,
        array $options = []
    ): static;

    /**
     * Use the log driver: record and log statements instead of running them.
     *
     * @param QueryLog|null $log
     *
     * @return static
     */
    public function useLog(?QueryLog $log = null): static;
}
