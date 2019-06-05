<?php

namespace Core\Database;

class PhpDbConnect extends DbConnect
{

    protected function __construct($settings)
    {
        parent::__construct($settings);
    }

    public function insert($queryRaw = '')
    {
        return $this->exec($queryRaw, 'insert');
    }

    public function update($queryRaw = '')
    {
        return $this->exec($queryRaw, 'update');
    }

    public function delete($queryRaw = '')
    {
        return $this->exec($queryRaw, 'delete');
    }

    public function alter($queryRaw = '')
    {
        return $this->exec($queryRaw, 'alter');
    }

    public function select($queryRaw = '')
    {
        return $this->query($queryRaw, 'select');
    }

    public function select_assoc($queryRaw = '')
    {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }

        return isset(self::$pdoInstance[$this->database]) && $this->result ? $this->result->fetch(
            PDO::FETCH_ASSOC
        ) : $this->result;
    }

    public function select_num($queryRaw = '')
    {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }

        return isset(self::$pdoInstance[$this->database]) && $this->result ? $this->result->fetch(
            PDO::FETCH_NUM
        ) : $this->result;
    }

    public function select_both($queryRaw = '')
    {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }

        return isset(self::$pdoInstance[$this->database]) && $this->result ? $this->result->fetch(
            PDO::FETCH_BOTH
        ) : $this->result;
    }

    public function select_object($queryRaw = '')
    {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }

        return isset(self::$pdoInstance[$this->database]) && $this->result ? $this->result->fetch(
            PDO::FETCH_OBJECT
        ) : $this->result;
    }

    public function select_lazy($queryRaw = '')
    {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw, 'select');
        }

        return isset(self::$pdoInstance[$this->database]) && $this->result ? $this->result->fetch(
            self::$pdoInstance[$this->database]->FETCH_LAZY
        ) : $this->result;
    }

    protected function queryValidation($queryRaw, $type)
    {
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

    public function consoleOut($outputIn, $typeIn = 'DB')
    {
        global $screenOut;
        $output = is_array($outputIn) || is_object($outputIn) ? json_encode(
            $outputIn
        ) : $outputIn;
        $type = addslashes($typeIn);
        if (isset($screenOut) && $screenOut) {
            echo "{$type}: {$output}\r\n";
        } else {
            $output = addslashes($output);
            $output = str_replace("\n", ' ', $output);
            echo "<script>console.log(\"{$type}: {$output}\")</script>";
        }

        return true;
    }

    public function sanitizeOutput($output)
    {
        if (is_array($output)) {
            $new_output = [];
            foreach ($output as $key => $value) {
                if (is_array($value)) {
                    $new_output[$key] = $this->sanitizeOutput($value);
                } else {
                    $new_output[$key] = stripslashes(
                        htmlentities(
                            str_replace('\r', '', $value),
                            ENT_HTML5,
                            'UTF-8',
                            false
                        )
                    );
                }
            }

            return $new_output;
        }

        return stripslashes(
            htmlentities(str_replace('\r', '', $output), ENT_HTML5, 'UTF-8',
                false)
        );
    }

}
