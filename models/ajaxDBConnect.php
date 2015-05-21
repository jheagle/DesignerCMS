<?php

header('Content-Type: application/json');

use DBConnect;

require_once($MODELS['dbConnectClass']);

class AjaxDBConnect extends DBConnect {

    private $jsonResult;

    protected function __construct($hostname = 'localhost', $database = '', $username = 'root', $password = '', $testing = true, $production = false) {
        parent::__construct($hostname, $database, $username, $password, $testing, $production);
    }

    public function insert($queryRaw = '') {
        $this->jsonResult = json_encode(parent::insert($queryRaw));
        echo $this->jsonResult;
    }

    public function update($queryRaw = '') {
        $this->jsonResult = json_encode(parent::update($queryRaw));
        echo $this->jsonResult;
    }

    public function delete($queryRaw = '') {
        $this->jsonResult = json_encode(parent::delete($queryRaw));
        echo $this->jsonResult;
    }

    public function select_assoc($queryRaw = '') {
        $this->jsonResult = json_encode(parent::select_assoc($queryRaw));
        echo $this->jsonResult;
    }

    protected function queryValidation($queryRaw, $type) {
        return parent::queryValidation($queryRaw, $type);
    }

}
