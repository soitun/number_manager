<?php 

require_once ROOT_PATH . 'providers/iProvider.php';

class Bandwidth implements iProvider {
    private $_curl;
    private $_settings;

    function __construct() {
        $general_settings = Settings::get_instance();
        $this->_settings = $general_settings->providers->bandwidth;
        $this->_init_curl();
    }

    function __destruct() {
        curl_close($this->_curl);
    }

    // Initializing curl with common params
    private function _init_curl() {
        $this->_curl = curl_init();

        curl_setopt_array($this->_curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => array('Content-Type: application/xml'),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => ROOT_PATH . "/certs/apitest.crt",
            CURLOPT_USERPWD => $this->_settings->username . ":" . $this->_settings->password
        ));
    }

    public function search($request_data) {
        if (isset($request_data['area_code']))
            $url = $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/availableNumbers?&areaCode=" . $request_data['area_code'];
        elseif (isset($request_data['city']) && isset($request_data['state']))
            $url = $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/availableNumbers?city=" . $request_data['city'] . "&state=" . $request_data['state'];
        else
            return array();

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => null
        ));

        return curl_exec($this->_curl);

        //$xmlresult = simplexml_load_string(curl_exec($this->_curl));

        if ($xmlresult->ResultCount != 0)
            return $xmlresult->TelephoneNumberList;
        else
            return array();
    }
}

?>