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
                'Content-Type: text/xml'
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
        $hosts = '';

        foreach ($request_data['data']['hosts'] as $host) {
            $hosts = $hosts . "&lt;host&gt;$host&lt;/host&gt;";
        }

$data = <<<XML
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Header></s:Header>
<s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
<processRequest xmlns="http://www.bandwidth.com/api/">
<xmlRequest>

&lt;basicNumberOrder xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.bandwidth.com/api/"&gt;
&lt;developerKey&gt;E46311BA5F241657E9354B2B2DA17620&lt;/developerKey&gt;
&lt;orderName/&gt;
&lt;extRefID/&gt;
&lt;numberIDs&gt;
&lt;id&gt;$identifier&lt;/id&gt;
&lt;/numberIDs&gt; 
&lt;subscriber&gt;VoIP&lt;/subscriber&gt;
&lt;endPoints&gt;
$hosts
&lt;/endPoints&gt;
&lt;/basicNumberOrder&gt;

</xmlRequest>
</processRequest>
</s:Body>
</s:Envelope>

XML;

        $url = $this->_settings->api_url . "numbers.asmx";

        curl_setopt_array($this->_curl, array(
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => "$data",
            CURLOPT_POST => true
        ));

        // This is a dumb response for testing purpose
        $return_arr['request_id'] = "4986219d-c105-4d1c-9d11-082b2ac3d3cb";
        $return_arr['order_id'] = "eb33808c-91f1-45eb-bfcf-445565bd3313";
        $return_arr['order_number'] = "1000000000001005281";
        $return_arr['order_name'] = "API Number Order 10/11/2013 10:00 PM";
        $return_arr['number_id'] = "CC88D310-A447-4C2C-A043-AD4771F1E778";
        return $return_arr;

        //return false;
 
        /*$curl_result = curl_exec($this->_curl);

        $xml = new SimpleXMLElement($curl_result); 
        $xml->registerXPathNamespace("soap", "http://www.w3.org/2003/05/soap-envelope");
        $response_body = $xml->xpath("//soap:Body")[0]->processRequestResponse->response;

        $return_arr = array();
        if ($response_body->status == "success") {
            $return_arr['status'] = (string)$response_body->status;
            $return_arr['request_id'] = (string)$response_body->requestID;
            $return_arr['order_id'] = (string)$response_body->numberOrder->orderID;
            $return_arr['order_number'] = (string)$response_body->numberOrder->orderNumber;
            $return_arr['order_name'] = (string)$response_body->numberOrder->orderName;
            $return_arr['number_id'] = (string)$response_body->numberOrder->telephoneNumbers->telephoneNumber->numberID;
            return $return_arr;
        } else return false;*/
    }
}

 ?>