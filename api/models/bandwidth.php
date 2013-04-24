<?php 

/**
 * Bandwidth SDK (Not sure if I can really called that a SDK)
 * @author Francis Genet
 * @package Number_manager_api
 */
class models_bandwidth {
    private $_curl;
    private $_settings;
    private $_obj_number;
    private $_obj_block;

    function __construct() {
        $general_settings = helper_settings::get_instance();
        $this->_settings = $general_settings->providers->{ENVIRONMENT}->bandwidth;
        $this->_init_curl();
    }

    function __destruct() {
        curl_close($this->_curl);
        $this->_db = null;
    }

    // Initializing curl with common params
    private function _init_curl() {
        $this->_curl = curl_init();

        curl_setopt_array($this->_curl, array(
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => array('Content-Type: application/xml'),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERPWD => $this->_settings->username . ":" . $this->_settings->password
        ));
    }
}

 ?>