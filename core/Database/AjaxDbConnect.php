<?php

namespace Core\Database;

class AjaxDbConnect extends PhpDbConnect
{

    private string $jsonResult;

    public function __construct($settings)
    {
        parent::__construct($settings);
    }

    /**
     * @param string $queryRaw
     * @return int
     */
    final public function insert(string $queryRaw = ''): int
    {
        $this->jsonResult = json_encode(parent::insert($queryRaw));

        return $this->jsonResult;
    }

    /**
     * @param string $queryRaw
     * @return int
     */
    final public function update(string $queryRaw = ''): int
    {
        $this->jsonResult = json_encode(parent::update($queryRaw));

        return $this->jsonResult;
    }

    /**
     * @param string $queryRaw
     * @return int
     */
    final public function delete(string $queryRaw = ''): int
    {
        $this->jsonResult = json_encode(parent::delete($queryRaw));

        return $this->jsonResult;
    }

    /**
     * @param string $queryRaw
     * @return int
     */
    final public function alter(string $queryRaw = ''): int
    {
        $this->jsonResult = json_encode(parent::alter($queryRaw));

        return $this->jsonResult;
    }

    /**
     * @param string $queryRaw
     * @return string|bool
     */
    final public function select(string $queryRaw = ''): string|bool
    {
        $this->jsonResult = json_encode(parent::selectAssoc($queryRaw));

        return $this->jsonResult;
    }

    /**
     * @param string $request
     * @return bool
     */
    final public function requestValidation(string $request): bool
    {
        $type = substr($request, 0, 6);
        $query = substr($request, 7);
        list($ex_type, $ex_query) = explode(':', $request, 2);

        return preg_match(
                '/^(insert|update|delete|select)/',
                $request
            ) && $type === $ex_type && $query === $ex_query;
    }

    /**
     * @param string $outputIn
     * @param string $typeIn
     * @return bool
     */
    final public function consoleOut($outputIn, $typeIn = 'DB'): bool
    {
        $output = is_array($outputIn) || is_object($outputIn) ? json_encode(
            $outputIn
        ) : $outputIn;
        $type = addslashes($typeIn);
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo "$type: $output\r\n";
        return true;
    }
}
