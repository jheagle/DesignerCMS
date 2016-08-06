<?php

if (!isset($ROOT)) {
    $ROOT = dirname(__DIR__);
}
require_once $ROOT . '/global_include.php';

abstract class DBConnect implements Potential {

    protected static $instance;
    private static $pdoInstance;
    private $database;
    public $production; // environment
    public $testing; // mode (truly run a query or not)
    private $queries;
    private $result;
    private $queryRaw;
    private $query;

    protected function __construct($hostname = 'localhost', $database = '', $username = 'root', $password = '', $testing = false, $production = true) {
        if (($hostname === 'localhost' || empty($hostname)) && empty($database) && ($username === 'root' || empty($username)) && empty($password)) {
            global $RESOURCES;
            include_once $RESOURCES['dbInfo'];
        }
        $this->database = $database;
        $this->testing = $testing;
        $this->production = $production;
        $this->queries = 0;
        if (empty($this->pdoInstance) || !is_array($this->pdoInstance)) {
            $this->pdoInstance = array();
        }
        try {
            if (empty($this->pdoInstance[$database])) {
                $this->pdoInstance[$database] = new PDO("mysql:host={$hostname};dbname={$database}", $username, $password);
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

    public function __destruct() {
        if ($this->testing || !$this->production) {
            $this->consoleOut("Completed {$this->queries} Queries");
        }
    }

    final public static function instantiateDB($hostname = 'localhost', $database = '', $username = 'root', $password = '', $testing = true, $production = false) {
        if (!is_array(self::$instance)) {
            self::$instance = array();
        }
        if (empty(self::$instance[$database])) {
            $class = get_called_class();
            self::$instance[$database] = new $class($hostname, $database, $username, $password, $testing, $production);
        }

        return self::$instance[$database];
    }

    private function __clone() {
        
    }

    protected function exec($queryRaw = '', $type = 'insert') {
        $count = 0;
        $query = empty($queryRaw) ? $this->query : $this->queryValidation($queryRaw, $type);
        if (empty($query)) {
            return;
        }
        try {
            if ($this->testing || !$this->production) {
                $this->consoleOut($query);
            }
            //TODO: ADD LOG
            if (!$this->testing && $this->pdoInstance[$this->database]) {
                $count = $this->pdoInstance[$this->database]->exec($query);
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

    protected function query($queryRaw = '', $type = 'select') {
        $query = empty($queryRaw) ? $this->query : $this->queryValidation($queryRaw, $type);
        if (empty($query)) {
            return;
        }
        try {
            if ($queryRaw === $this->queryRaw && isset($this->pdoInstance[$this->database])) {
                $this->result = $this->pdoInstance[$this->database]->query($this->query);
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

    public function lastInsertId($name = null) {
        return $this->pdoInstance[$this->database]->lastInsertId($name);
    }

    public function rowCount() {
        return $this->pdoInstance[$this->database]->rowCount();
    }

    public function sanitizeInput($input, $escape = true, &$type = null) {
        if (is_array($input)) {
            $type = array();
            $new_input = array();
            foreach ($input as $key => $value) {
                if (is_array($value)) {
                    $new_input[$key] = $this->sanitizeInput($value, $escape, $type[$key]);
                } else {
                    $value = html_entity_decode($value, ENT_HTML5, 'UTF-8');
                    $new_input[$key] = filterVarType($value, $escape, $type[$key]);
                }
            }

            return $new_input;
        }
        $input = html_entity_decode($input, ENT_HTML5, 'UTF-8');

        return filterVarType($input, $escape, $type);
    }

    public function filterVarType($input, $escape = true, &$type = null) {
        $input = trim($val);
        $length = strlen($input);
        if ($length === strlen((int) $input)) {
            $input = (int) $val;
            $type = PDO::PARAM_INT;
        } elseif ($length === strlen((float) $input)) {
            $input = (float) $input;
            $type = PDO::PARAM_STR;
        } elseif (preg_match('/^(true|false)$/i', $input)) {
            $input = strtolower($input) === 'true';
            $type = PDO::PARAM_BOOL;
        } elseif (json_decode($input)) {
            $val = json_decode($input);
            if (is_array($input)) {
                $input = (array) sanitizeInput($input, $escape);
            } elseif (is_object($input)) {
                $input = (object) sanitizeInput((array) $input, $escape);
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

    public function camelToUnderscore($input) {
        return ltrim(strtolower(preg_replace('/[A-Z0-9]/', '_$0', $input)), '_');
    }

    public function underscoreToCamel($input) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
    }

    public function __toString() {
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

class UnitTest implements Potential {

    private static $instance;
    private static $breakpoints;
    private static $origFiles;
    private static $copyFiles;
    private $filename;
    private $db;
    private $currFunction;
    private $prevData;
    private $curreData;
    private $prevLine;
    private $currLine;
    private $pause;

    private function __construct(&$db, $filename = __FILE__) {
        $this->db = $db;
        $this->filename = $filename;
        if (!is_array($this->origFiles) || !is_array($this->copyFiles)) {
            $this->origFiles = $this->copyFiles = array();
        }
        if (strpos($filename, 'utest') === false) {
            $this->origFiles[$filename] = $filename;
            $this->copyFiles[$filename] = preg_replace('/\\.[^.\\s]{3,4}$/', '.utest.php', $filename);
            if (!copy($this->origFiles[$filename], $this->copyFiles[$filename])) {
                $this->db->consoleOut("~!Failed to Copy File: [{$filename}]!~", 'PHP');
            } else {
                $this->db->consoleOut("Created File Copy: [{$filename}]", 'PHP');
            }
            header('Location: ' . basename($this->copyFiles[$filename]));
            die();
        }
    }

    private function __destruct() {
        if (strpos($this->filename, 'utest') === true) {
            self::$instance = null;
            foreach ($this->copyFiles as &$file) {
                $this->db->consoleOut(unlink($file) ? "Removed File: [{$file}]" : "~!Failed to Remove File: [{$file}]!~", 'PHP');
                unset($file);
            }
            $this->copyFile = null;
        }
    }

    public static function instantiateTest(&$db, $filename = __FILE__) {
        if (self::$instance == null) {
            self::$instance = new self($db, $filename);
        }

        return self::$instance;
    }

    public function __get($property) {
        if (!isset($this->{$property})) {
            return;
        }
        if (is_array($this->{$property})) {
            $new_output = array();
            foreach ($this->{$property} as $key => $value) {
                if (is_array($value)) {
                    $new_output[$key] = $this->{$property}[$key];
                } else {
                    $new_output[$key] = stripslashes(htmlentities(str_replace('\r', '', $value), ENT_HTML5, 'UTF-8', false));
                }
            }

            return $new_output;
        }

        return stripslashes(htmlentities(str_replace('\r', '', $this->{$property}), ENT_HTML5, 'UTF-8', false));
    }

    public function set($property, $input) {
        if (!property_exists($this, $property)) {
            return;
        }
        if (is_array($input)) {
            $new_input = array();
            foreach ($input as $key => $value) {
                $new_input[$key] = addslashes(html_entity_decode(trim($value), ENT_HTML5, 'UTF-8'));
            }
            $this->{$property} = $new_input;
        }
        $this->{$property} = addslashes(html_entity_decode(trim($input), ENT_HTML5, 'UTF-8'));
    }

    public function traceProcesses() {
        echo '<br/>CLASS: ' . __CLASS__;
        echo '<br/>DIR: ' . __DIR__;
        echo '<br/>FILE: ' . __FILE__;
        echo '<br/>FUNCTION: ' . __FUNCTION__;
        echo '<br/>LINE: ' . __LINE__;
        echo '<br/>METHOD: ' . __METHOD__;
        echo '<br/>NAMESPACE: ' . __NAMESPACE__;
        echo '<br/>TRAIT: ' . __TRAIT__;
    }

    private function __clone() {
        
    }

    public function __toString() {
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
