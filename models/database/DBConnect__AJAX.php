<?php

require_once $DATABASE['DBConnect'];

class AjaxDBConnect extends DBConnect
{

    private $jsonResult;

    protected function __construct($settings)
    {
        parent::__construct($settings);
    }

    public function insert($queryRaw = '')
    {
        $this->jsonResult = json_encode(parent::insert($queryRaw));

        return $this->jsonResult;
    }

    public function update($queryRaw = '')
    {
        $this->jsonResult = json_encode(parent::update($queryRaw));

        return $this->jsonResult;
    }

    public function delete($queryRaw = '')
    {
        $this->jsonResult = json_encode(parent::delete($queryRaw));

        return $this->jsonResult;
    }

    public function alter($queryRaw = '')
    {
        $this->jsonResult = json_encode(parent::alter($queryRaw));

        return $this->jsonResult;
    }

    public function select($queryRaw = '')
    {
        $this->jsonResult = json_encode(parent::select_assoc($queryRaw));

        return $this->jsonResult;
    }

    public function requestValidation($request)
    {
        $type = substr($request, 0, 6);
        $query = substr($request, 7);
        list($ex_type, $ex_query) = explode(':', $request, 2);

        return preg_match(
            '/^(insert|update|delete|select)/',
            $request
          ) && $type === $ex_type && $query === $ex_query;
    }

    protected function queryValidation($queryRaw, $type)
    {
        return parent::queryValidation($queryRaw, $type);
    }

    public function consoleOut($outputIn, $typeIn = 'DB')
    {
        $output = is_array($outputIn) || is_object($outputIn) ? json_encode(
          $outputIn
        ) : $outputIn;
        $type = addslashes($typeIn);
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo "{$type}: {$output}\r\n";
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
