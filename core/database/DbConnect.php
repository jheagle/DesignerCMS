<?php

namespace Core\Database;

use Core\DataTypes\Potential;
use PDO;
use PDOException;

abstract class DbConnect implements Potential
{

    protected static $instance;

    protected static $pdoInstance;

    protected $database;

    public $production; // environment

    public $testing; // mode (truly run a query or not)

    protected $queries;

    protected $result;

    protected $queryRaw;

    protected $query;

    protected function __construct($settings)
    {
        $localHosts = ['127.0.0.1', '::1'];
        if (in_array($_SERVER['SERVER_ADDR'], $localHosts, true) && in_array(
                $_SERVER['REMOTE_ADDR'],
                $localHosts,
                true
            )) {
            //    header('Content-Type: application/json'); This is my debuggin trick, but if I have xdebug then this ruins it
            $testing = true;
            $production = false;
        }

        if (!isset($username)) {
            $username = 'root';
        }

        if (!isset($hostname)) {
            $hostname = 'localhost';
        }
        extract($settings);
        if (($hostname === 'localhost' || empty($hostname)) && empty($database) && ($username === 'root' || empty($username)) && empty($password) && $production) {
            global $RESOURCES;
            include_once $RESOURCES['dbInfo'];
        }
        $this->database = $database;
        $this->testing = $testing;
        $this->production = $production;
        $this->queries = 0;
        if (empty(self::$pdoInstance) || !is_array(self::$pdoInstance)) {
            self::$pdoInstance = [];
        }
        try {
            if (empty(self::$pdoInstance[$database])) {
                self::$pdoInstance[$database] = new PDO(
                    "mysql:host={$hostname};dbname={$database}",
                    $username,
                    $password
                );
            }
            if ($testing || !$production) {
                $this->consoleOut("Connected to database ({$database})");
            }
            //TODO: ADD LOG
        } catch (PDOException $e) {
            if ($testing || !$production) {
                $this->consoleOut($e->getMessage());
            }
            //TODO: ADD LOG
        }
    }

    public function __destruct()
    {
        if ($this->testing || !$this->production) {
            $this->consoleOut("Completed {$this->queries} Queries");
        }
    }

    final public static function instantiateDB()
    {
        $args = func_get_args();
        $default_args = [
            'string' => [
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
        foreach ($args as $arg => $val) {
            $type = gettype($val);
            if (array_key_exists($type, $default_args)) {
                $key = key($default_args[$type]);
                array_shift($default_args[$type]);
                $settings[$key] = $val;
            }
        }
        foreach ($default_args as $defaults) {
            $settings = array_merge($defaults, $settings);
        }

        if (!is_array(self::$instance)) {
            self::$instance = [];
        }
        if (empty(self::$instance[$settings['database']])) {
            $class = get_called_class();
            self::$instance[$settings['database']] = new $class($settings);
        }

        return self::$instance[$settings['database']];
    }

    private function __clone()
    {

    }

    public function __call($name, $arguments)
    {
        return (!method_exists($this, $name) && method_exists(
                self::$pdoInstance[$this->database],
                $name
            )) ? call_user_func_array(
            [self::$pdoInstance[$this->database], $name],
            $arguments
        ) : false;
    }

    public static function __callStatic($name, $arguments)
    {
        return (!method_exists(DBConnect, $name) && method_exists(
                self::$pdoInstance[static::database],
                $name
            )) ? call_user_func_array(
            [self::$pdoInstance[static::database], $name],
            $arguments
        ) : false;
    }

    protected function exec($queryRaw = '', $type = 'insert')
    {
        $count = 0;
        $query = empty($queryRaw) ? $this->query : $this->queryValidation(
            $queryRaw,
            $type
        );
        if (empty($query)) {
            return;
        }
        try {
            if ($this->testing || !$this->production) {
                $this->consoleOut($query);
            }
            //TODO: ADD LOG
            if (!$this->testing && self::$pdoInstance[$this->database]) {
                $count = self::$pdoInstance[$this->database]->exec($query);
            }
            //TODO: Create psuedo insert and record for testing mode
            $this->queries += $count;

            return $count;
        } catch (PDOException $e) {
            if ($this->testing || !$this->production) {
                $this->consoleOut($e->getMessage());
            }

            //TODO: ADD LOG
            return 0;
        }
    }

    abstract protected function insert($queryRaw);

    abstract protected function update($queryRaw);

    abstract protected function delete($queryRaw);

    abstract protected function alter($queryRaw);

    protected function query($queryRaw = '', $type = 'select')
    {
        $query = empty($queryRaw) ? $this->query : $this->queryValidation(
            $queryRaw,
            $type
        );
        if (empty($query)) {
            return;
        }
        try {
            if ($queryRaw === $this->queryRaw && isset(self::$pdoInstance[$this->database])) {
                $this->result = self::$pdoInstance[$this->database]->query(
                    $this->query
                );
            }
            if ($this->testing || !$this->production) {
                $this->consoleOut($this->query);
            }

            return $this->result;
        } catch (PDOException $e) {
            if ($this->testing || !$this->production) {
                $this->consoleOut($e->getMessage());
            }

            //TODO: ADD LOG
            return 0;
        }
    }

    abstract protected function select($queryRaw);

    abstract protected function queryValidation($queryRaw, $type);

    abstract protected function consoleOut($outputIn, $typeIn);

    public function lastInsertId($name = null)
    {
        return self::$pdoInstance[$this->database]->lastInsertId($name);
    }

    public function rowCount()
    {
        return self::$pdoInstance[$this->database]->rowCount();
    }

    public function sanitizeInput($input, $escape = true, &$type = null)
    {
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
                    $new_input[$key] = filterVarType(
                        $value,
                        $escape,
                        $type[$key]
                    );
                }
            }

            return $new_input;
        }
        $input = html_entity_decode($input, ENT_HTML5, 'UTF-8');

        return filterVarType($input, $escape, $type);
    }

    public function filterVarType($input, $escape = true, &$type = null)
    {
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
            $val = json_decode($input);
            if (is_array($input)) {
                $input = (array)sanitizeInput($input, $escape);
            } elseif (is_object($input)) {
                $input = (object)sanitizeInput((array)$input, $escape);
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

    abstract public function sanitizeOutput($output);

    public function camelToUnderscore($input)
    {
        return ltrim(
            strtolower(preg_replace('/[A-Z0-9]/', '_$0', $input)),
            '_'
        );
    }

    public function underscoreToCamel($input)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
    }

    public function __toString(): string
    {
        $string = '';
        foreach (get_object_vars($this) as $k => $v) {
            if (empty($string)) {
                $string = __CLASS__ . '( ';
            } else {
                $string .= ', ';
            }
            $string .= "{$k}: {$v}";
        }

        return $string . ' )';
    }

}

