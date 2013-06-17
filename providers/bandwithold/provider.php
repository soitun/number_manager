<?php 

class providers_bandwidthold_provider extends providers_aprovider {
    private $_curl;

    function __construct() {
        parent::__construct();
        $this->_init_curl();
    }

    // Initializing curl with common params
    private function _init_curl() {
        $this->_curl = curl_init();

        curl_setopt_array($this->_curl, array(
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => array('Content-Type: text/xml'),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ));
    }

    public function create($area_code) {
        if (!$area_code)
            die("Empty area code\n");

        echo $area_code . '\r\n';
    }
}