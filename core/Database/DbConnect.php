<?php

namespace Core\Database;

use Core\Adaptors\Config;
use Core\Adaptors\Vendor\Database\Connection;
use Core\Adaptors\Vendor\Logger\Logger;
use Core\Contracts\LazyAssignable;
use Core\DataTypes\Interfaces\Potential;
use Core\Traits\LazyAssignment;
use InvalidArgumentException;
use LogicException;
use PDO;
use PDOException;
use SensitiveParameter;
use Throwable;

/**
 *
 */
abstract class DbConnect implements LazyAssignable, Potential
{
    use LazyAssignment;

    /**
     * @var DbConnect[] $instance
     */
    public static array $instance = [];

    /**
     * The open connections (adaptors around PDO, or the log driver), shared by key.
     *
     * @var Connection[] $connections
     */
    public static array $connections = [];

    /**
     * Which shared connection the static calls (__callStatic) use: the last one made.
     */
    private static string $staticConnectionKey = '';

    /**
     * This object's key in $connections: driver, host and database, so two servers with the same database name never
     * share a connection.
     */
    protected string $connectionKey = '';

    /**
     * The driver: a PDO driver such as 'mysql', or 'log' (record and log statements, run nothing; see QueryLog).
     */
    public string $connection = 'mysql';

    public string $hostname = 'localhost';

    public string $database = '';

    public bool $production = true; // environment

    public bool $testing = false; // testing mode: statements are not run (it uses the log driver)

    public int $queries = 0;

    /**
     * Rows affected or returned by the last statement (rowCount() reads it; PDO itself has no row count).
     */
    protected int $lastRowCount = 0;

    /**
     * Savepoints made so far, for unique names (nested transaction() calls).
     */
    protected int $savepoints = 0;

    public mixed $result = null;

    public string $queryRaw = '';

    public string $query = '';

    /**
     * The values bound to the last query (with $queryRaw, it says whether nextRow() continues or starts again).
     */
    public array $queryParams = [];

    /**
     * Connect with keyed settings. instantiateDB() is the usual way in: it fills in what is left out from config and
     * reuses the connection.
     *
     * @param array $settings connection, hostname, database, username, password, testing, production. Only the
     *  class members are kept; username and password are used to connect and never stored or logged.
     *
     * @throws InvalidArgumentException When no database or no user is named.
     * @throws PDOException When the connection fails (logged first).
     */
    public function __construct(#[SensitiveParameter] array $settings = [])
    {
        $this->applyMemberSettings($settings);
        // Testing mode means the log driver: statements are recorded and logged, never run
        if ($this->testing) {
            $this->connection = self::LOG_DRIVER;
        }
        if ($this->connection === self::LOG_DRIVER) {
            $this->openLog();
            return;
        }
        if ($this->database === '') {
            throw new InvalidArgumentException('No database to connect to: set DB_DATABASE, or pass "database"');
        }
        $username = (string)($settings['username'] ?? '');
        if ($username === '') {
            throw new InvalidArgumentException('No database user: set DB_USERNAME, or pass "username"');
        }
        $this->useConnectionKey();
        try {
            self::$connections[$this->connectionKey] ??= Connection::instantiate()->connect(
                "$this->connection:host=$this->hostname;dbname=$this->database",
                $username,
                (string)($settings['password'] ?? ''),
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            // Only the message and code: the exception's trace holds the call arguments, which include the password
            // wherever zend.exception_ignore_args is off (#[SensitiveParameter] hides it from new traces)
            Logger::error("Unable to connect to $this->database@$this->hostname", [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
        if ($this->testing || !$this->production) {
            $this->consoleOut("Connected to database ($this->database)");
        }
        Logger::info("Connected to database ($this->database@$this->hostname)");
    }

    /**
     * @param string $queryRaw
     * @return mixed
     */
    abstract public function insert(string $queryRaw, array $params = []): mixed;

    /**
     * @param string $queryRaw
     * @return mixed
     */
    abstract public function update(string $queryRaw, array $params = []): mixed;

    /**
     * @param string $queryRaw
     * @return mixed
     */
    abstract public function delete(string $queryRaw, array $params = []): mixed;

    /**
     * @param string $queryRaw
     * @return mixed
     */
    abstract public function alter(string $queryRaw, array $params = []): mixed;

    /**
     * @param string $queryRaw
     * @return mixed
     */
    abstract public function select(string $queryRaw, array $params = []): mixed;

    /**
     * @param string $outputIn
     * @param string $typeIn
     * @return mixed
     */
    abstract public function consoleOut(string $outputIn, string $typeIn): mixed;

    /**
     * @param mixed $output
     * @return mixed
     */
    abstract public function sanitizeOutput(mixed $output): mixed;

    /**
     *
     */
    public function __destruct()
    {
        if ($this->testing || !$this->production) {
            $this->consoleOut("Completed $this->queries Queries");
        }
    }

    /**
     * The shared connection to a database: made once per class and database, then reused.
     *
     * @param array $settings Keyed: connection, hostname, database, username, password, testing, production. What is
     *  left out comes from config (core/config/database.php and system.php, so from .env).
     *
     * @return static
     *
     * @throws InvalidArgumentException When no database or no user is named.
     * @throws PDOException When the connection fails.
     */
    final public static function instantiateDB(#[SensitiveParameter] array $settings = []): static
    {
        $settings = array_replace(static::configuredSettings(), $settings);
        $key = static::class . "|{$settings['connection']}|{$settings['hostname']}|{$settings['database']}";

        return self::$instance[$key] ??= new static($settings);
    }

    /**
     * The connection settings config gives: the current connection's from database.php, testing and production from
     * system.php.
     *
     * @return array
     */
    public static function configuredSettings(): array
    {
        $connection = Config::get('database.connection', 'mysql');

        return [
            'connection' => $connection,
            'hostname' => (string)Config::get("database.connections.$connection.hostname", 'localhost'),
            'database' => (string)Config::get("database.connections.$connection.database", ''),
            'username' => (string)Config::get("database.connections.$connection.username", ''),
            'password' => (string)Config::get("database.connections.$connection.password", ''),
            'testing' => (bool)Config::get('system.testing', false),
            'production' => (bool)Config::get('system.production', true),
        ];
    }

    /**
     * The driver name that records and logs statements instead of running them.
     */
    public const LOG_DRIVER = 'log';

    /**
     * Key this object's connection by driver, host and database, and make it the one static calls use.
     *
     * @return void
     */
    private function useConnectionKey(): void
    {
        $this->connectionKey = "$this->connection|$this->hostname|$this->database";
        self::$staticConnectionKey = $this->connectionKey;
    }

    /**
     * Open the log driver: no server, no credentials.
     *
     * @return void
     */
    private function openLog(): void
    {
        $this->useConnectionKey();
        self::$connections[$this->connectionKey] ??= Connection::instantiate()->useLog();
        Logger::info("Using the log driver: statements are recorded and logged, not run ($this->database)");
    }

    /**
     * Forget every shared connection (tests, or a long-running process that changes its settings). The next
     * instantiateDB() connects again.
     *
     * @return void
     */
    public static function disconnectAll(): void
    {
        self::$instance = [];
        self::$connections = [];
        self::$staticConnectionKey = '';
    }

    /**
     * This object's connection (the adaptor around PDO, or the log driver), or null when there is none.
     *
     * @return Connection|null
     */
    final public function connection(): ?Connection
    {
        return self::$connections[$this->connectionKey] ?? null;
    }

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments = []): mixed
    {
        $connection = $this->connection();

        return $connection !== null && !method_exists($this, $name)
            ? $connection->$name(...$arguments)
            : false;
    }

    /**
     * Call a connection method on the last connection made, e.g. PhpDbConnect::setAttribute(...).
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments = []): mixed
    {
        $connection = self::$connections[self::$staticConnectionKey] ?? null;

        return $connection !== null && !method_exists(DbConnect::class, $name)
            ? $connection->$name(...$arguments)
            : false;
    }

    /**
     * Run one statement: prepare it, then execute it with its values bound, so a value never becomes part of the SQL
     * (no injection, no quoting). Every query in DbConnect goes through here.
     *
     * @param string $sql With ? or :name placeholders for the values.
     * @param array $params The values: a list for ?, keyed by name for :name.
     *
     * @return object The executed statement (a PDOStatement, or the log driver's or a fake's stand-in).
     *
     * @throws PDOException When the statement fails (logged with the SQL, never the values).
     * @throws LogicException When there is no connection.
     */
    final public function statement(string $sql, array $params = []): object
    {
        $connection = $this->requireConnection();
        if ($this->testing || !$this->production) {
            $this->consoleOut($sql);
        }
        try {
            $statement = $connection->prepare($sql);
            $statement->execute($params);
        } catch (PDOException $e) {
            // The values are left out on purpose: they can be personal data or secrets
            Logger::error('Statement failed', ['sql' => $sql, 'message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw $e;
        }
        $this->lastRowCount = (int)$statement->rowCount();
        $this->queries++;
        Logger::debug('Statement run', ['sql' => $sql]);

        return $statement;
    }

    /**
     * Run a statement that changes data or schema (INSERT, UPDATE, DELETE, ALTER...).
     *
     * @param string $sql
     * @param array $params
     *
     * @return int The rows it affected.
     */
    final public function exec(string $sql, array $params = []): int
    {
        return (int)$this->statement($sql, $params)->rowCount();
    }

    /**
     * Run a query and keep its result for reading row by row (the select* methods do this).
     *
     * @param string $sql
     * @param array $params
     *
     * @return object The executed statement, also kept in $this->result.
     */
    final public function query(string $sql, array $params = []): object
    {
        $this->result = $this->statement($sql, $params);
        $this->queryRaw = $this->query = $sql;
        $this->queryParams = $params;

        return $this->result;
    }

    /**
     * The next row of a query, read one call at a time: `while ($row = $db->nextRow($sql)) { ... }`. A different SQL
     * or different values start the query again; when the rows run out it returns false, and the next call (even with
     * the same SQL and values) runs the query again, so a second loop sees the rows as they are then.
     *
     * @param string $sql
     * @param array $params
     * @param int $mode A PDO::FETCH_* mode.
     *
     * @return mixed The row, or false when there are no more.
     */
    final public function nextRow(string $sql, array $params = [], int $mode = PDO::FETCH_ASSOC): mixed
    {
        if (!isset($this->result) || $sql !== $this->queryRaw || $params !== $this->queryParams) {
            $this->query($sql, $params);
        }
        $row = $this->result->fetch($mode);
        if ($row === false) {
            // Used up: forget it, so the same query asked again runs again instead of returning false forever
            $this->result = null;
        }

        return $row;
    }

    /**
     * All rows of a query, each keyed by column name.
     *
     * @param string $sql
     * @param array $params
     *
     * @return array<int, array<string, mixed>>
     */
    final public function fetchAll(string $sql, array $params = []): array
    {
        return $this->statement($sql, $params)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * The first row of a query, keyed by column name, or null when there is none.
     *
     * @param string $sql
     * @param array $params
     *
     * @return array<string, mixed>|null
     */
    final public function fetchOne(string $sql, array $params = []): ?array
    {
        return $this->statement($sql, $params)->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * The first column of the first row (e.g. a COUNT), or null when there is no row.
     *
     * @param string $sql
     * @param array $params
     *
     * @return mixed
     */
    final public function fetchValue(string $sql, array $params = []): mixed
    {
        $value = $this->statement($sql, $params)->fetchColumn();

        return $value === false ? null : $value;
    }

    /**
     * Start a transaction: nothing until commit() is kept.
     *
     * @return bool
     */
    final public function begin(): bool
    {
        return (bool)$this->requireConnection()->beginTransaction();
    }

    /**
     * Keep everything since begin().
     *
     * @return bool
     */
    final public function commit(): bool
    {
        return (bool)$this->requireConnection()->commit();
    }

    /**
     * Undo everything since begin().
     *
     * @return bool
     */
    final public function rollBack(): bool
    {
        return (bool)$this->requireConnection()->rollBack();
    }

    /**
     * Whether a transaction is open.
     *
     * @return bool
     */
    final public function inTransaction(): bool
    {
        return (bool)$this->requireConnection()->inTransaction();
    }

    /**
     * Run $work as one unit: all of it is kept, or none of it. Commits when $work returns; rolls back and rethrows
     * when it throws. $work is given this DbConnect; transaction() returns what $work returns.
     *
     * Inside another transaction it is a savepoint of that one: a failure undoes only this part (then the error goes
     * on to the outer work, which may catch it and carry on, or fail too and undo everything); success keeps this
     * part for the outer transaction to commit. If a rollback itself fails, that is logged and the original error is
     * thrown.
     *
     * @param callable $work
     *
     * @return mixed
     *
     * @throws Throwable Whatever $work threw, after the rollback.
     */
    final public function transaction(callable $work): mixed
    {
        if ($this->inTransaction()) {
            return $this->savepoint($work);
        }
        $this->begin();
        try {
            $result = $work($this);
            $this->commit();

            return $result;
        } catch (Throwable $e) {
            try {
                if ($this->inTransaction()) {
                    $this->rollBack();
                }
            } catch (Throwable $rollbackError) {
                // Keep the real cause: report the failed rollback, then throw what $work threw
                Logger::error('Rollback failed', ['message' => $rollbackError->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * Run $work in a savepoint of the open transaction: released when it returns, rolled back to when it throws.
     *
     * @param callable $work
     *
     * @return mixed
     *
     * @throws Throwable Whatever $work threw, after rolling back to the savepoint.
     */
    private function savepoint(callable $work): mixed
    {
        $name = 'dcms_savepoint_' . ++$this->savepoints;
        $this->statement("SAVEPOINT $name");
        try {
            $result = $work($this);
            $this->statement("RELEASE SAVEPOINT $name");

            return $result;
        } catch (Throwable $e) {
            try {
                $this->statement("ROLLBACK TO SAVEPOINT $name");
            } catch (Throwable $rollbackError) {
                Logger::error('Rollback to savepoint failed', ['message' => $rollbackError->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * The connection, or a clear error when there is none.
     *
     * @return Connection
     *
     * @throws LogicException
     */
    private function requireConnection(): Connection
    {
        return $this->connection()
            ?? throw new LogicException('No database connection: connect with instantiateDB() first');
    }

    /**
     * @param string|null $name
     * @return string
     */
    final public function lastInsertId(?string $name = null): string
    {
        return (string)$this->connection()?->lastInsertId($name);
    }

    /**
     * @return int
     */
    final public function rowCount(): int
    {
        // Only a statement has a row count (PDO has no rowCount()), so this is the last statement's
        return $this->lastRowCount;
    }

    /**
     * @param mixed $input
     * @param bool $escape
     * @param string|null $type
     * @return string|array|bool|int|object|float|null
     */
    final public function sanitizeInput(
        mixed $input,
        bool $escape = true,
        mixed &$type = null
    ): string|array|bool|int|null|object|float {
        if (is_array($input)) {
            $type = [];
            $new_input = [];
            foreach ($input as $key => $value) {
                if (is_array($value)) {
                    $new_input[$key] = $this->sanitizeInput(
                        $value,
                        $escape,
                        $type[$key]
                    );
                } else {
                    $value = html_entity_decode($value, ENT_HTML5, 'UTF-8');
                    $new_input[$key] = $this->filterVarType(
                        $value,
                        $escape,
                        $type[$key]
                    );
                }
            }

            return $new_input;
        }
        $input = html_entity_decode($input, ENT_HTML5, 'UTF-8');

        return $this->filterVarType($input, $escape, $type);
    }

    /**
     * @param string $val
     * @param bool $escape
     * @param int|null $type
     * @return string|array|bool|int|object|float|null
     */
    final public function filterVarType(
        string $val,
        bool $escape = true,
        ?int &$type = null
    ): string|array|bool|int|null|object|float {
        $input = trim($val);
        $length = strlen($input);
        if ($length === strlen((int)$input)) {
            $input = (int)$val;
            $type = PDO::PARAM_INT;
        } elseif ($length === strlen((float)$input)) {
            $input = (float)$input;
            $type = PDO::PARAM_STR;
        } elseif (preg_match('/^(true|false)$/i', $input)) {
            $input = strtolower($input) === 'true';
            $type = PDO::PARAM_BOOL;
        } elseif (json_decode($input)) {
            $input = json_decode($val);
            if (is_array($input)) {
                $input = (array)$this->sanitizeInput($input, $escape);
            } elseif (is_object($input)) {
                $input = (object)$this->sanitizeInput((array)$input, $escape);
            }
            $type = PDO::PARAM_LOB;
            if ($input === null) {
                $input = null;
                $type = PDO::PARAM_NULL;
            }
        } else {
            if ($escape) {
                $input = addslashes($input);
            }
            $type = PDO::PARAM_STR;
        }

        return $input;
    }

    /**
     * @param string $input
     * @return string
     */
    final public function camelToUnderscore(string $input): string
    {
        return ltrim(
            strtolower(preg_replace('/[A-Z0-9]/', '_$0', $input)),
            '_'
        );
    }

    /**
     * @param string $input
     * @return string
     */
    final public function underscoreToCamel(string $input): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $string = '';
        foreach (get_object_vars($this) as $k => $v) {
            if (empty($string)) {
                $string = __CLASS__ . '( ';
            } else {
                $string .= ', ';
            }
            $string .= "$k: $v";
        }

        return $string . ' )';
    }

}

