<?php

require_once($MODELS['dbConnectClass']);

class AjaxDBConnect extends DBConnect {

    private $jsonResult;

    protected function __construct($hostname = 'localhost', $database = '', $username = 'root', $password = '', $testing = true, $production = false) {
        parent::__construct($hostname, $database, $username, $password, $testing, $production);
    }

    public function insert($queryRaw = '') {
        $this->jsonResult = json_encode(parent::insert($queryRaw));
        return $this->jsonResult;
    }

    public function update($queryRaw = '') {
        $this->jsonResult = json_encode(parent::update($queryRaw));
        return $this->jsonResult;
    }

    public function delete($queryRaw = '') {
        $this->jsonResult = json_encode(parent::delete($queryRaw));
        return $this->jsonResult;
    }

    public function select($queryRaw = '') {
        $this->jsonResult = json_encode(parent::select_assoc($queryRaw));
        return $this->jsonResult;
    }

    public function requestValidation($request) {
        $type = substr($request, 0, 6);
        $query = substr($request, 7);
        list($ex_type, $ex_query) = explode(':', $request, 2);
        return preg_match('/^(insert|update|delete|select)/', $request) && $type === $ex_type && $query === $ex_query;
    }

    protected function queryValidation($queryRaw, $type) {
        return parent::queryValidation($queryRaw, $type);
    }

    public function consoleOut($outputIn, $typeIn = 'DB') {
        $output = is_array($outputIn) || is_object($outputIn) ? addslashes(json_encode($outputIn)) : addslashes($outputIn);
        $type = addslashes($typeIn);
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo "{$type}: {$output}|\r\n<br>";
    }

}
