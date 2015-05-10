<?php

//$screenOut = true;

class DBConnect {

    protected static $instance;
    private static $pdoInstance;
    private $database;
    public $production; // environment
    public $testing; // mode (truly run a query or not)
    private $queries;
    private $result;
    private $queryRaw;
    private $query;

    protected function __construct($hostname = 'localhost', $database = '', $username = 'root', $password = '', $testing = true, $production = false) {
        if (($hostname === 'localhost' || empty($hostname)) && empty($database) && ($username === 'root' || empty($username)) && empty($password)) {
            include_once($_SERVER['DOCUMENT_ROOT'] . '/projects/php/addressbook/resources/dbInfo.php');
        }
//        $testing = true;
//        $production = false;
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
            self::$instance[$database] = new self($hostname, $database, $username, $password, $testing, $production);
        }
        return self::$instance[$database];
    }

    private function __clone() {
        
    }

    private function exec($queryRaw = '', $type = 'insert') {
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
            return -1;
        }
    }

    public function insert($queryRaw = '') {
        return $this->exec($queryRaw, 'insert');
    }

    public function update($queryRaw = '') {
        return $this->exec($queryRaw, 'update');
    }

    public function delete($queryRaw = '') {
        return $this->exec($queryRaw, 'delete');
    }

    private function query($queryRaw = '', $type = 'select') {
        $query = empty($queryRaw) ? $this->query : $this->queryValidation($queryRaw, $type);
        if (empty($query)) {
            return;
        }
        try {
            if ($queryRaw === $this->queryRaw && $this->pdoInstance[$this->database]) {
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
            return -1;
        }
    }

    public function select($queryRaw = '') {
        return $this->query($queryRaw, 'select');
    }

    public function select_assoc($queryRaw = '') {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }
        return $this->pdoInstance[$this->database] && $this->result ? $this->result->fetch(PDO::FETCH_ASSOC) : $this->result;
    }

    public function select_num($queryRaw = '') {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }
        return $this->pdoInstance[$this->database] && $this->result ? $this->result->fetch(PDO::FETCH_NUM) : $this->result;
    }

    public function select_both($queryRaw = '') {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }
        return $this->pdoInstance[$this->database] && $this->result ? $this->result->fetch(PDO::FETCH_BOTH) : $this->result;
    }

    public function select_object($queryRaw = '') {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }
        return $this->pdoInstance[$this->database] && $this->result ? $this->result->fetch(PDO::FETCH_OBJECT) : $this->result;
    }

    public function select_lazy($queryRaw = '') {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }
        return $this->pdoInstance[$this->database] && $this->result ? $result->fetch($this->pdoInstance[$this->database]->FETCH_LAZY) : $result;
    }

    private function queryValidation($queryRaw, $type) {
        if ($queryRaw === $this->queryRaw) {
            return $this->query;
        }
        $this->queryRaw = $queryRaw;
        return $this->query = $this->queryRaw; // remove thise once complete function
        /*        if (!preg_match("`?\d*[a-zA-Z][0-9a-zA-Z$_]*`?", $tableName)) {
          return;
          }
          if (!preg_match("(`?\d*[a-zA-Z][0-9,a-z,A-Z$_]*`?,?)+", $columnNames) && !is_array($columnNames)) {
          return;
          }
          if (!preg_match("('?\d*[a-zA-Z][0-9,a-z,A-Z$_]*'?,?)+", $whereClauses) && !is_array($whereClauses)) {
          return;
          }
          if (!preg_match("('?\d*[a-zA-Z][0-9,a-z,A-Z$_]*'?,?)+", $newValues) && !is_array($newValues)) {
          return;
          }
          $table = sanitize_input($tableName, false);
          $columns = sanitize_input($columnNames, false);
          $values = sanitize_input($newValues, false);
          $where = sanitize_input($whereClauses, false);
          if (is_array($columns)) {
          $columns = implode(',', $columns);
          }
          if (is_array($where)) {
          $where = implode(',', $where);
          }
          if (is_array($values)) {
          $values = implode('),(', $values);
          }
          if (!empty($where)) {
          $where = " WHERE {$where}";
          }
          switch ($type) {
          case 'select':
          $query = "SELECT {$columns} FROM {$table}{$where}{$orderBy}{$offset}{$limit}";
          break;
          case 'insert':
          $query = "INSERT INTO {$table}({$columns}) VALUES ({$values}){$where}";
          break;
          case 'update':
          break;
          case 'delete':
          break;
          default:
          }
          return $this->query; */
    }

    public function consoleOut($outputIn, $typeIn = 'DB') {
        global $screenOut;
        $output = is_array($outputIn) || is_object($outputIn) ? addslashes(json_encode($outputIn)) : addslashes($outputIn);
        $type = addslashes($typeIn);
        if (isset($screenOut) && $screenOut) {
            echo "{$type}: {$output}|\r\n<br>";
        } else {
            echo "<script>console.log(\"{$type}: {$output}\")</script>";
        }
    }

    public function sanitizeInput($input, $escape = true) {
        if (is_array($input)) {
            $new_input = array();
            foreach ($input as $key => $value) {
                /*
                 * Cannot use this because the idiots at Network Solutions do not offer a server with a version of PHP higher than 5.3
                  $new_input[$key] = $escape ? addslashes(html_entity_decode(trim($value), ENT_HTML5, 'UTF-8')) : html_entity_decode(trim($value), ENT_HTML5, 'UTF-8');
                 * 
                 */
                $new_input[$key] = $escape ? addslashes(html_entity_decode(trim($value), ENT_QUOTES, 'UTF-8')) : html_entity_decode(trim($value), ENT_QUOTES, 'UTF-8');
            }
            return $new_input;
        }
        /*
         * Cannot use this because the idiots at Network Solutions do not offer a server with a version of PHP higher than 5.3
          return $escape ? addslashes(html_entity_decode(trim($input), ENT_HTML5, 'UTF-8')) : html_entity_decode(trim($input), ENT_HTML5, 'UTF-8');
         * 
         */
        return $escape ? addslashes(html_entity_decode(trim($input), ENT_QUOTES, 'UTF-8')) : html_entity_decode(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public function sanitizeOutput($output) {
        if (is_array($output)) {
            $new_output = array();
            foreach ($output as $key => $value) {
                if (is_array($value)) {
                    $new_output[$key] = $this->sanitizeOutput($value);
                } else {
                    /*
                     * Cannot use this because the idiots at Network Solutions do not offer a server with a version of PHP higher than 5.3
                      $new_output[$key] = stripslashes(htmlentities(str_replace('\r', '', $value), ENT_HTML5, 'UTF-8', false));
                     * 
                     */
                    $new_output[$key] = stripslashes(htmlentities(str_replace('\r', '', $value), ENT_QUOTES, 'UTF-8', false));
                }
            }
            return $new_output;
        }
        /*
         * Cannot use this because the idiots at Network Solutions do not offer a server with a version of PHP higher than 5.3
          return stripslashes(htmlentities(str_replace('\r', '', $output), ENT_HTML5, 'UTF-8', false));
         * 
         */
        return stripslashes(htmlentities(str_replace('\r', '', $output), ENT_QUOTES, 'UTF-8', false));
    }

    public function camelToUnderscore($input) {
        return ltrim(strtolower(preg_replace('/[A-Z0-9]/', '_$0', $input)), '_');
    }

    public function underscoreToCamel($input) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
    }

}

class UnitTest {

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
            return null;
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
            return null;
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

}
