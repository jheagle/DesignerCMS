<?php

namespace Core\Database;

use PDO;

/**
 *
 */
class PhpDbConnect extends DbConnect
{
    /**
     * @param string $queryRaw
     * @return int
     */
    public function insert(string $queryRaw = ''): int
    {
        return $this->exec($queryRaw);
    }

    /**
     * @param string $queryRaw
     * @return int
     */
    public function update(string $queryRaw = ''): int
    {
        return $this->exec($queryRaw, 'update');
    }

    /**
     * @param string $queryRaw
     * @return int
     */
    public function delete(string $queryRaw = ''): int
    {
        return $this->exec($queryRaw, 'delete');
    }

    /**
     * @param string $queryRaw
     * @return int
     */
    public function alter(string $queryRaw = ''): int
    {
        return $this->exec($queryRaw, 'alter');
    }

    /**
     * @param string $queryRaw
     * @return mixed
     */
    public function select(string $queryRaw = ''): mixed
    {
        return $this->query($queryRaw);
    }

    /**
     * @param string $queryRaw
     * @return mixed
     */
    public function selectAssoc(string $queryRaw = ''): mixed
    {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw);
        }

        return isset(self::$pdoInstance[$this->database]) && $this->result ? $this->result->fetch(
            PDO::FETCH_ASSOC
        ) : $this->result;
    }

    /**
     * @param string $queryRaw
     * @return mixed
     */
    public function selectColumn(string $queryRaw = ''): mixed
    {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw);
        }

        return isset(self::$pdoInstance[$this->database]) && $this->result ? $this->result->fetch(
            PDO::FETCH_COLUMN
        ) : $this->result;
    }

    /**
     * @param string $queryRaw
     * @return mixed
     */
    public function selectNum(string $queryRaw = ''): mixed
    {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw);
        }

        return isset(self::$pdoInstance[$this->database]) && $this->result ? $this->result->fetch(
            PDO::FETCH_NUM
        ) : $this->result;
    }

    /**
     * @param string $queryRaw
     * @return mixed
     */
    public function selectBoth(string $queryRaw = ''): mixed
    {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw);
        }

        return isset(self::$pdoInstance[$this->database]) && $this->result ? $this->result->fetch(
            PDO::FETCH_BOTH
        ) : $this->result;
    }

    /**
     * @param string $queryRaw
     * @return mixed
     */
    public function selectObject(string $queryRaw = ''): mixed
    {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw);
        }

        return isset(self::$pdoInstance[$this->database]) && $this->result ? $this->result->fetch(
            PDO::FETCH_OBJ
        ) : $this->result;
    }

    /**
     * @param string $queryRaw
     * @return mixed
     */
    public function selectLazy(string $queryRaw = ''): mixed
    {
        if (empty($this->result) || $queryRaw !== $this->queryRaw) {
            $this->query($queryRaw);
        }

        return isset(self::$pdoInstance[$this->database]) && $this->result ? $this->result->fetch(
            self::$pdoInstance[$this->database]->FETCH_LAZY
        ) : $this->result;
    }

    /**
     * @param string $queryRaw
     * @param string $type
     * @return string
     */
    public function queryValidation(string $queryRaw, string $type): string
    {
        if ($queryRaw === $this->queryRaw) {
            return $this->query;
        }
        $this->queryRaw = $queryRaw;

        return $this->query = $this->queryRaw; // remove this once complete function
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

    /**
     * @param string $outputIn
     * @param string $typeIn
     * @return bool
     */
    public function consoleOut(string $outputIn, string $typeIn = 'DB'): bool
    {
        global $screenOut;
        $output = is_array($outputIn) || is_object($outputIn) ? json_encode(
            $outputIn
        ) : $outputIn;
        $type = addslashes($typeIn);
        if (isset($screenOut) && $screenOut) {
            echo "$type: $output\r\n";
        } else {
            $output = addslashes($output);
            $output = str_replace("\n", ' ', $output);
            echo "<script>console.log(\"$type: $output\")</script>";
        }

        return true;
    }

    /**
     * @param mixed $output
     * @return string|array
     */
    public function sanitizeOutput(mixed $output): string|array
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
