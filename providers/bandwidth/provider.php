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

            // Numbers array
            $arr_numbers = array();
            foreach ($xml_number_result->TelephoneNumberList->TelephoneNumber as $number) {
                // Creating number model
                $obj_number = new models_number("bandwidth");
                $obj_number->set_number($number);
                $obj_number->set_city($city);
                $obj_number->set_state($state);
                $obj_number->insert();

                // building the number array
                $arr_numbers[] = (int)$number;
            }

            // Sort from lowest to highest
            $arr_numbers = array_unique($arr_numbers, SORT_NUMERIC);
            sort($arr_numbers);

            print_r($arr_numbers);

            // Blocks
            $cur_block = new models_block("bandwidth");
            $cur_block->set_start_number($arr_numbers[0]);
            $previous_number = null;
            for ($i=0; $i < count($arr_numbers); $i++) { 
                $current = (int)substr($arr_numbers[$i], -4);
                $next = (int)substr($arr_numbers[$i+1], -4);

                echo "Entering loop\n";
                $nplus1 = $arr_numbers[$i+1];
                $n = $arr_numbers[$i];
                echo "arr + 1 is: $nplus1\n";
                echo "next is : $next\n";

                if($next) {
                    echo "in next \n";
                    if($next == $current + 1) {
                        echo "inside group for $n\n";
                        continue;
                    } else {
                        $cur_block->set_end_number($arr_numbers[$i]);
                        if ($cur_block->insert()) {
                            $cur_block = null;
                            $cur_block = new models_block("bandwidth");
                            $cur_block->set_start_number($arr_numbers[$i+1]);
                        } else 
                            exit('Could not save a block');
                    }
                } else {
                    echo "not in next \n";
                    $cur_block->set_end_number($arr_numbers[$i]);
                    if ($cur_block->insert()) {
                        continue;
                    } else 
                        exit('Could not save a block');
                }
            }

            sleep($this->_settings->wait_timer);
        }
    }
}

?>