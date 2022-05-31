<?php

namespace Core\Database;

use Core\Adaptors\Config;
use Core\Adaptors\Vendor\Logger\Logger;
use Core\Contracts\LazyAssignable;
use Core\DataTypes\Interfaces\Potential;
use Core\Traits\LazyAssignment;
use Core\Utilities\Functional\Pure;
use PDO;
use PDOException;

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
     * @var PDO[] $pdoInstance
     */
    public static array $pdoInstance = [];

    private static string $staticDatabase;

    public string $database;

    public bool $production; // environment

    public bool $testing; // mode (truly run a query or not)

    public int $queries = 0;

    public mixed $result;

    public string $queryRaw = '';

    public string $query = '';

    /**
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->applyMemberSettings($settings);
        $localHosts = ['127.0.0.1', '::1'];
        if (in_array(Pure::dotGet($_SERVER, 'SERVER_ADDR', ''), $localHosts, true) && in_array(
                $_SERVER['REMOTE_ADDR'],
                $localHosts,
                true
            )) {
            //    header('Content-Type: application/json'); This is my debugging trick, but if I have xdebug then this ruins it
            $this->testing = true;
            $this->production = false;
        }
        $connection = Pure::dotGet($settings, 'connection', 'mysql');
        $username = Pure::dotGet($settings, 'username', 'root');
        $password = Pure::dotGet($settings, 'password', '');
        $hostname = Pure::dotGet($settings, 'hostname', 'localhost');
        $this->database = Pure::dotGet($settings, 'database', '127.0.0.1');
        self::$staticDatabase = $this->database;
        if (($hostname === 'localhost' || empty($hostname)) && empty($database) && ($username === 'root' || empty($username)) && empty($password) && $this->production) {
            global $RESOURCES;
            include_once $RESOURCES['dbInfo'];
        }
        if (empty(self::$pdoInstance) || !is_array(self::$pdoInstance)) {
            self::$pdoInstance = [];
        }
        try {
            if (empty(self::$pdoInstance[$this->database])) {
                self::$pdoInstance[$this->database] = new PDO(
                    "$connection:host=$hostname;dbname=$this->database",
                    $username,
                    $password
                );
            }
            if ($this->testing || !$this->production) {
                $this->consoleOut("Connected to database ($this->database)");
            }
            Logger::info("Connected to database ($this->database@$hostname)");
        } catch (PDOException $e) {
            if ($this->testing || !$this->production) {
                $this->consoleOut($e->getMessage());
            }
            Logger::error("Unable to connect to $this->database@$hostname", [
                'Exception' => $e
            ]);
        }
    }

    /**
     * @param string $queryRaw
     * @return mixed
     */
    abstract public function insert(string $queryRaw): mixed;

    /**
     * @param string $queryRaw
     * @return mixed
     */
    abstract public function update(string $queryRaw): mixed;

    /**
     * @param string $queryRaw
     * @return mixed
     */
    abstract public function delete(string $queryRaw): mixed;

    /**
     * @param string $queryRaw
     * @return mixed
     */
    abstract public function alter(string $queryRaw): mixed;

    /**
     * @param string $queryRaw
     * @return mixed
     */
    abstract public function select(string $queryRaw): mixed;

    /**
     * @param string $queryRaw
     * @param string $type
     * @return string
     */
    abstract public function queryValidation(string $queryRaw, string $type): string;

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
     * @return static
     */
    final public static function instantiateDB(): self
    {
        $args = func_get_args();
        $defaultArgs = [
            'string' => [
                'connection' => 'mysql',
                'hostname' => 'localhost',
                'database' => '',
                'username' => 'root',
                'password' => '',
            ],
            'boolean' => [
                'testing' => false,
                'production' => true,
            ],
        ];
        $settings = [];
        if (count($args) === 1 and is_array($args[0])) {
            $settings = array_shift($args);
        }
        foreach ($args as $val) {
            $type = gettype($val);
            if (array_key_exists($type, $defaultArgs)) {
                $key = key($defaultArgs[$type]);
                array_shift($defaultArgs[$type]);
                $settings[$key] = $val;
            }
        }
        foreach ($defaultArgs as $defaults) {
            $settings = array_merge($defaults, $settings);
        }

        if (empty(self::$instance[$settings['database']])) {
            $class = get_called_class();
            self::$instance[$settings['database']] = new $class($settings);
        }

        return self::$instance[$settings['database']];
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
        return (!method_exists($this, $name) && method_exists(
                self::$pdoInstance[$this->database],
                $name
            )) ? call_user_func_array(
            [self::$pdoInstance[$this->database], $name],
            $arguments
        ) : false;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments = []): mixed
    {
        return (!method_exists(DbConnect::class, $name) && method_exists(
                self::$pdoInstance[self::$staticDatabase],
                $name
            )) ? call_user_func_array(
            [self::$pdoInstance[self::$staticDatabase], $name],
            $arguments
        ) : false;
    }

    /**
     * @param string $queryRaw
     * @param string $type
     * @return int
     */
    final public function exec(string $queryRaw = '', string $type = 'insert'): int
    {
        $count = 0;
        $query = empty($queryRaw) ? $this->query : $this->queryValidation(
            $queryRaw,
            $type
        );
        if (empty($query)) {
            return 0;
        }
        try {
            if ($this->testing || !$this->production) {
                $this->consoleOut($query);
            }
            Logger::info("Running query:", [
                'Query' => $query,
            ]);
            if (!$this->testing && self::$pdoInstance[$this->database]) {
                $count = self::$pdoInstance[$this->database]->exec($query);
            }
            Logger::info("Query complete:", [
                'Query' => $query,
            ]);
            $this->queries += $count;

            return $count;
        } catch (PDOException $e) {
            if ($this->testing || !$this->production) {
                $this->consoleOut($e->getMessage());
            }
            Logger::error("Failed to execute query", [
                'Query' => $query,
                'Exception' => $e,
                'Raw' => $queryRaw,
            ]);
            return 0;
        }
    }

    /**
     * @param string|null $name
     * @return string
     */
    final public function lastInsertId(string $name = null): string
    {
        return self::$pdoInstance[$this->database]->lastInsertId($name);
    }

    /**
     * @return int
     */
    final public function rowCount(): int
    {
        return self::$pdoInstance[$this->database]->rowCount();
    }

    /**
     * @param string $queryRaw
     * @param string $type
     * @return mixed
     */
    final public function query(string $queryRaw = '', string $type = 'select'): mixed
    {
        $query = empty($queryRaw) ? $this->query : $this->queryValidation(
            $queryRaw,
            $type
        );
        if (empty($query)) {
            return 0;
        }
        try {
            if ($queryRaw === $this->queryRaw && isset(self::$pdoInstance[$this->database])) {
                $this->result = self::$pdoInstance[$this->database]->query(
                    $this->query
                );
            }
            Logger::info("Running query:", [
                'Query' => $query,
            ]);
            if ($this->testing || !$this->production) {
                $this->consoleOut($this->query);
            }
            Logger::info("Query complete:", [
                'Query' => $query,
            ]);

            return $this->result;
        } catch (PDOException $e) {
            if ($this->testing || !$this->production) {
                $this->consoleOut($e->getMessage());
            }
            Logger::error("Failed to execute query", [
                'Query' => $query,
                'Exception' => $e,
                'Raw' => $queryRaw,
            ]);
            return 0;
        }
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
        string &$type = null
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
        int &$type = null
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

