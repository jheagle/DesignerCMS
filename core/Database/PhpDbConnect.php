<?php

namespace Core\Database;

use PDO;

/**
 *
 */
class PhpDbConnect extends DbConnect
{
    /**
     * Add rows: the SQL with ? or :name placeholders, the values bound separately.
     *
     * @param string $queryRaw
     * @param array $params
     *
     * @return int The rows affected.
     */
    public function insert(string $queryRaw = '', array $params = []): int
    {
        return $this->exec($queryRaw, $params);
    }

    /**
     * Change rows: the SQL with ? or :name placeholders, the values bound separately.
     *
     * @param string $queryRaw
     * @param array $params
     *
     * @return int The rows affected.
     */
    public function update(string $queryRaw = '', array $params = []): int
    {
        return $this->exec($queryRaw, $params);
    }

    /**
     * Remove rows: the SQL with ? or :name placeholders, the values bound separately.
     *
     * @param string $queryRaw
     * @param array $params
     *
     * @return int The rows affected.
     */
    public function delete(string $queryRaw = '', array $params = []): int
    {
        return $this->exec($queryRaw, $params);
    }

    /**
     * Change the schema (ALTER, CREATE, DROP...): the SQL with ? or :name placeholders, the values bound separately.
     *
     * @param string $queryRaw
     * @param array $params
     *
     * @return int The rows affected.
     */
    public function alter(string $queryRaw = '', array $params = []): int
    {
        return $this->exec($queryRaw, $params);
    }

    /**
     * Run a query and keep its result (read it with the select* methods, or use fetchAll()/fetchOne()).
     *
     * @param string $queryRaw
     * @param array $params
     *
     * @return object The executed statement.
     */
    public function select(string $queryRaw = '', array $params = []): mixed
    {
        return $this->query($queryRaw, $params);
    }

    /**
     * The next row of a query, keyed by column name: `while ($row = $db->selectAssoc($sql, $params))`. False when there are no more.
     *
     * @param string $queryRaw
     * @param array $params
     *
     * @return mixed
     */
    public function selectAssoc(string $queryRaw = '', array $params = []): mixed
    {
        return $this->nextRow($queryRaw, $params, PDO::FETCH_ASSOC);
    }

    /**
     * The next row of a query, its first column only: `while ($row = $db->selectColumn($sql, $params))`. False when there are no more.
     *
     * @param string $queryRaw
     * @param array $params
     *
     * @return mixed
     */
    public function selectColumn(string $queryRaw = '', array $params = []): mixed
    {
        return $this->nextRow($queryRaw, $params, PDO::FETCH_COLUMN);
    }

    /**
     * The next row of a query, as a list: `while ($row = $db->selectNum($sql, $params))`. False when there are no more.
     *
     * @param string $queryRaw
     * @param array $params
     *
     * @return mixed
     */
    public function selectNum(string $queryRaw = '', array $params = []): mixed
    {
        return $this->nextRow($queryRaw, $params, PDO::FETCH_NUM);
    }

    /**
     * The next row of a query, keyed by name and by position: `while ($row = $db->selectBoth($sql, $params))`. False when there are no more.
     *
     * @param string $queryRaw
     * @param array $params
     *
     * @return mixed
     */
    public function selectBoth(string $queryRaw = '', array $params = []): mixed
    {
        return $this->nextRow($queryRaw, $params, PDO::FETCH_BOTH);
    }

    /**
     * The next row of a query, as an object: `while ($row = $db->selectObject($sql, $params))`. False when there are no more.
     *
     * @param string $queryRaw
     * @param array $params
     *
     * @return mixed
     */
    public function selectObject(string $queryRaw = '', array $params = []): mixed
    {
        return $this->nextRow($queryRaw, $params, PDO::FETCH_OBJ);
    }

    /**
     * The next row of a query, as a lazy row object: `while ($row = $db->selectLazy($sql, $params))`. False when there are no more.
     *
     * @param string $queryRaw
     * @param array $params
     *
     * @return mixed
     */
    public function selectLazy(string $queryRaw = '', array $params = []): mixed
    {
        return $this->nextRow($queryRaw, $params, PDO::FETCH_LAZY);
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
