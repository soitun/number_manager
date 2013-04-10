<?php 

class providers_bandwidth_provider implements providers_iprovider {
    private $_curl;
    private $_settings;
    private $_db;

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
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => ROOT_PATH . "/certs/apitest.crt",
            CURLOPT_USERPWD => $this->_settings->username . ":" . $this->_settings->password
        ));
    }

    public function sync($area_code) {
        if (!$area_code)
            die("Empty area code\n");

        $url = $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/availableNpaNxx?&areaCode=" . $area_code;

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => null
        ));

        $xml_result = simplexml_load_string(curl_exec($this->_curl));

        foreach ($xml_result->AvailableNpaNxxList->AvailableNpaNxx as $result) {
            $city = $result->City;
            $state = $result->State;
            $npanxx = $result->Npa . $result->Nxx;

            $url = $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/availableNumbers?&npaNxx=" . $npanxx;

            curl_setopt_array($this->_curl, array(
                CURLOPT_URL => $url
            ));

            $xml_number_result = simplexml_load_string(curl_exec($this->_curl));

            // Creating first block
            $obj_block = new models_block("bandwidth");
            foreach ($xml_number_result->TelephoneNumberList->TelephoneNumber as $number) {
                // Creating number model
                $obj_number = new models_number("bandwidth");
                $obj_number->set_number($number);
                $obj_number->set_city($city);
                $obj_number->set_state($state);
                $obj_number->insert();


            }
            die();
        }
    }

    /*public function search($request_data) {
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
    }*/
}

?>