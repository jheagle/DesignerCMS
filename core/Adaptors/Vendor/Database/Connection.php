<?php

namespace Core\Adaptors\Vendor\Database;

use Core\Adaptors\Adaptor;
use SensitiveParameter;

/**
 * The database connection behind DbConnect, as an adaptor (config/adaptors.php maps it to \PDO), so it can be swapped
 * or faked like the other packages: Connection::fake([...]) in a test, or the log driver (QueryLog) in config.
 * Calls such as exec(), query(), prepare() and lastInsertId() pass through to what it wraps.
 *
 * @package Core\Adaptors\Vendor\Database
 *
 * @property \PDO|QueryLog $classInstance
 */
class Connection extends Adaptor implements ConnectionInterface
{
    /**
     * Open the connection. The credentials are used to build it and then forgotten: nothing on this object keeps the
     * password (with() stores its arguments, so they are cleared after the build).
     *
     * @param string $dsn e.g. 'mysql:host=localhost;dbname=shop'
     * @param string $username
     * @param string $password
     * @param array $options PDO options
     *
     * @return static
     */
    final public function connect(
        string $dsn,
        string $username,
        #[SensitiveParameter] string $password,
        array $options = []
    ): static {
        try {
            $this->with($dsn, $username, $password, $options)->build();
        } finally {
            $this->constructorArgs = [];
        }

        return $this;
    }

    /**
     * Pass a call to the connection exactly as made. The adaptors' usual forwarding fills in default parameters by
     * reflection, which PDO's variadic methods (query()'s fetch modes) don't accept.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->classInstance->$name(...$arguments);
    }

    /**
     * Stand in a QueryLog (the log driver): statements are recorded and logged, never run.
     *
     * @param QueryLog|null $log
     *
     * @return static
     */
    final public function useLog(?QueryLog $log = null): static
    {
        $this->classInstance = $log ?? new QueryLog();

        return $this;
    }
}
