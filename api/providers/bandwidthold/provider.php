<?php 

/**
 * Bandwidth provider
 * @author Francis Genet
 * @package Number_manager_api
 */
class providers_bandwidthold_provider {

    private $_curl;
    private $_settings;

    function __construct() {
        $general_settings = helper_settings::get_instance();
        $this->_settings = $general_settings->providers->{ENVIRONMENT}->bandwidthold;
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
            //CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml', 
                'X-BWC-IN-Control-Processing-Type: process'
            ),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ));
    }

    public function check_status($number, $country) {
        // get the country obj for the prefix
        $country_obj = new models_country($country);

        // Checking if the number is well formed or not
        if (strlen($number) > 11)
            return false;
        elseif (strlen($number) == 11)
            $number = substr($number, 1);

        // Retrieve the number identifier
        // For this SDK it looks like : 48A15B4F-5D2F-4314-B14A-675F29345282
        $number_obj = new models_number($country_obj->get_prefix() . $number, $country);
        $id = $number_obj->get_number_identifier();

        $data = "<?xml version=\"1.0\"?>
        <getTelephoneNumber xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns=\"http://www.bandwidth.com/api/\">
            <developerKey>" . $this->_settings->developer_key . "</developerKey>
            <getType>numberID</getType>
            <getValue>" . $id . "</getValue>
        </getTelephoneNumber>";

        $url = $this->_settings->api_url . "numbers.api";

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => $data
        ));

        $result = simplexml_load_string(curl_exec($this->_curl));
        
        if ($result->telephoneNumber->status == "Available")
            return $id;
        else return false;
    }

    public function order($request_data, $identifier) {
        // We need to retrieve the number id first
        $timestamp = time();
        $data = "<?xml version=\"1.0\"?>
        <basicNumberOrder xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns=\"http://www.bandwidth.com/api/\">
            <developerKey>" . $this->_settings->developer_key . "</developerKey>
            <orderName>2600hz-" . $timestamp . "</orderName>
            <extRefID>" . $request_data['extrefid'] . "</extRefID>
            <numberIDs>
                <id>" . $identifier . "</id>
            </numberIDs>
            <subscriber>" . $request_data['subscriber'] . "</subscriber>
            <endPoints>
                <host>sipproxy001-aa-ord.2600hz.com</host>
                <host>sipproxy001-aa-dfw.2600hz.com</host>
            </endPoints>
        </basicNumberOrder>";

        $url = $this->_settings->api_url . "numbers.api";

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => $data
        ));

        $result = simplexml_load_string(curl_exec($this->_curl));

        if ($result->status == "success")
            return true;
        else return false;
    }
}

 ?>