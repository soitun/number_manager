<?php 

class providers_bandwidth_provider implements providers_iprovider {
    private $_curl;
    private $_provider_settings;
    private $_database_settings;
    private $_db;

    function __construct() {
        $general_settings = helper_settings::get_instance();
        $this->_provider_settings = $general_settings->providers->{ENVIRONMENT}->bandwidth;
        $this->_database_settings = $general_settings->database;
        $this->_init_curl();
        $this->_init_mysql();
    }

    function __destruct() {
        curl_close($this->_curl);
        $this->_db = null;
    }

    private function _init_mysql() {
        // Set the DSN (the string that determines what driver to user and how)
        $dsn = "mysql:host=" . $this->_database_settings->database_host . ";dbname=" . $this->_database_settings->database_name . ";charset=" . $this->_database_settings->database_charset;
        // Set the driver parameters
        $drvr_params = array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        // Creating a connexion
        $this->_db = new PDO($dsn, $this->_database_settings->database_user, $this->_database_settings->database_password, $drvr_params);
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
            CURLOPT_USERPWD => $this->_provider_settings->username . ":" . $this->_provider_settings->password
        ));
    }

    public function sync($area_code) {
        if (!$area_code)
            die("Empty area code\n");

        $url = $this->_provider_settings->api_url . "accounts/" . $this->_provider_settings->account_id . "/availableNpaNxx?&areaCode=" . $area_code;

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

            $url = $this->_provider_settings->api_url . "accounts/" . $this->_provider_settings->account_id . "/availableNumbers?&npaNxx=" . $npanxx;

            curl_setopt_array($this->_curl, array(
                CURLOPT_URL => $url
            ));

            $xml_number_result = simplexml_load_string(curl_exec($this->_curl));

            $group_size = 0;
            $previous_number = null;
            foreach ($xml_number_result->TelephoneNumberList->TelephoneNumber as $number) {
                // Adding number
                try {
                    $stmt_number = $this->_db->prepare("INSERT INTO `numbers`(`number`, `provider`, `cache_update`, `city`, `state`) VALUES(?, ?, now(), ?, ?)");
                    $stmt_number->execute(array($number, "bandwidth", $city, $state));
                } catch (PDOException $e) {
                    echo($e->getMessage() . "\n");
                }

                // Adding block
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