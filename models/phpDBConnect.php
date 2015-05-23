<?php

require_once($MODELS['dbConnectClass']);

class PHPDBConnect extends DBConnect {

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
        parent::__construct($hostname, $database, $username, $password, $testing, $production);
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

    public function select($queryRaw = '') {
        return $this->query($queryRaw, 'select');
    }

    public function select_assoc($queryRaw = '') {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }
        return isset($this->pdoInstance[$this->database]) && $this->result ? $this->result->fetch(PDO::FETCH_ASSOC) : $this->result;
    }

    public function select_num($queryRaw = '') {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }
        return isset($this->pdoInstance[$this->database]) && $this->result ? $this->result->fetch(PDO::FETCH_NUM) : $this->result;
    }

    public function select_both($queryRaw = '') {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }
        return isset($this->pdoInstance[$this->database]) && $this->result ? $this->result->fetch(PDO::FETCH_BOTH) : $this->result;
    }

    public function select_object($queryRaw = '') {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }
        return isset($this->pdoInstance[$this->database]) && $this->result ? $this->result->fetch(PDO::FETCH_OBJECT) : $this->result;
    }

    public function select_lazy($queryRaw = '') {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }
        return isset($this->pdoInstance[$this->database]) && $this->result ? $this->result->fetch($this->pdoInstance[$this->database]->FETCH_LAZY) : $this->result;
    }

    protected function queryValidation($queryRaw, $type) {
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
        $output = is_array($outputIn) || is_object($outputIn) ? json_encode($outputIn) : $outputIn;
        $type = addslashes($typeIn);
        if (isset($screenOut) && $screenOut) {
            echo "{$type}: {$output}\r\n";
        } else {
            $output = addcslashes($output);
            echo "<script>console.log(\"{$type}: {$output}\")</script>";
        }
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

}
