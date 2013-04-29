<?php 

class providers_bandwidth_provider implements providers_iprovider {
    private $_curl;
    private $_settings;
    private $_obj_number;
    private $_obj_block;
    private $_obj_tollfree;

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
            CURLOPT_CAINFO => CERTS_PATH . "apitest.crt",
            CURLOPT_USERPWD => $this->_settings->username . ":" . $this->_settings->password
        ));
    }

    private function _insert_block($arr_numbers) {
        // Blocks
        $this->_obj_block->set_start_number($arr_numbers[0]);
        $previous_number = null;
        for ($i=0; $i < count($arr_numbers); $i++) { 
            $current = (int)substr($arr_numbers[$i], -4);
            $next = (int)substr($arr_numbers[$i+1], -4);

            if($next) {
                if($next == $current + 1) {
                    continue;
                } else {
                    $this->_obj_block->set_end_number($arr_numbers[$i]);
                    if ($this->_obj_block->insert()) {
                        $this->_obj_block->set_start_number($arr_numbers[$i+1]);
                    } else 
                        exit('Could not save a block');
                }

                $this->_obj_block->set_end_number($arr_numbers[$i]);
                if ($this->_obj_block->insert()) {
                    continue;
                } else 
                    exit('Could not save a block');
            }
        }
    }

    private function _get_available_npa_nxx($area_code) {
        $url = $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/availableNpaNxx?&areaCode=" . $area_code;

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => null
        ));

        return simplexml_load_string(curl_exec($this->_curl));
    }

    private function _get_available_numbers_by_npa_nxx($npanxx) {
        $url = $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/availableNumbers?&npaNxx=" . $npanxx;

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => null
        ));

        return simplexml_load_string(curl_exec($this->_curl));
    }

    public function update($area_code) {
        if (!$area_code)
            die("Empty area code\n");

        // Creating models
        $this->_obj_number = new models_number("bandwidth");
        $this->_obj_block = new models_block("bandwidth");

        $this->_obj_number->set_db_name('US_' . $area_code);

        $xml_result = $this->_get_available_npa_nxx($area_code);
        foreach ($xml_result->AvailableNpaNxxList->AvailableNpaNxx as $result) {
            $city = $result->City;
            $state = $result->State;
            $npanxx = $result->Npa . $result->Nxx;

            // Get number list for this NpaNxx
            $xml_number_result = $this->_get_available_numbers_by_npa_nxx($npanxx);

            $this->_obj_number->start_transaction();

            if (!$this->_obj_number->delete_like_number('1' . $npanxx))
                $this->_obj_number->rollback();

            // Numbers array
            $arr_numbers = array();
            foreach ($xml_number_result->TelephoneNumberList->TelephoneNumber as $number) {
                $this->_obj_number->set_number('1' . $number);
                $this->_obj_number->set_city($city);
                $this->_obj_number->set_state($state);
                $this->_obj_number->insert();

                // building the number array
                $arr_numbers[] = (int)'1' . $number;
            }

            // Sort from lowest to highest
            $arr_numbers = array_unique($arr_numbers, SORT_NUMERIC);
            sort($arr_numbers);

            $this->_obj_block->start_transaction();

            if (!$this->_obj_block->delete_like_number('1' . $npanxx))
                $this->_obj_block->rollback();

            $this->_insert_block($arr_numbers);

            $this->_obj_number->commit();
            $this->_obj_block->commit();

            sleep($this->_settings->wait_timer);
        }
    }

    public function create($area_code) {
        if (!$area_code)
            die("Empty area code\n");

        // Creating models
        $this->_obj_number = new models_number("bandwidth");
        $this->_obj_block = new models_block("bandwidth");

        $xml_result = $this->_get_available_npa_nxx($area_code);
        foreach ($xml_result->AvailableNpaNxxList->AvailableNpaNxx as $result) {
            $city = $result->City;
            $state = $result->State;
            $npanxx = $result->Npa . $result->Nxx;

            $xml_number_result = $this->_get_available_numbers_by_npa_nxx($npanxx);

            $this->_obj_number->set_or_create_db('US_' . $area_code);

            // Numbers array
            $arr_numbers = array();
            foreach ($xml_number_result->TelephoneNumberList->TelephoneNumber as $number) {
                $this->_obj_number->set_number('1' . $number);
                $this->_obj_number->set_city($city);
                $this->_obj_number->set_state($state);
                $this->_obj_number->insert();

                // building the number array
                $arr_numbers[] = (int)'1' . $number;
            }

            // Sort from lowest to highest
            $arr_numbers = array_unique($arr_numbers, SORT_NUMERIC);
            sort($arr_numbers);

            $this->_insert_block($arr_numbers);

            sleep($this->_settings->wait_timer);
        }
    }

    private function _get_tollfree_numbers($area_code) {
        $target = substr($area_code, 0, 2) . '*';
        $url = $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/availableNumbers?&tollFreeWildCardPattern=" . $target;

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => null
        ));

        return simplexml_load_string(curl_exec($this->_curl));
    }

    public function create_tollfree($area_code) {
        if (!$area_code)
            die("Empty area code\n");

        $this->_obj_tollfree = new models_tollfree("bandwidth");

        $xml_result = $this->_get_tollfree_numbers($area_code);
        foreach ($xml_result->TelephoneNumberList->TelephoneNumber as $number) {
            $this->_obj_tollfree->set_number('1' . $number);
            $this->_obj_tollfree->insert();
        }
    }
}

?>