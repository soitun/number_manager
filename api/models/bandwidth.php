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

    public function create_site($name, $description, $add_number, $add_street, $add_city, $add_state, $add_zip) {
        $data = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?> 
        <Site>
            <Name>" . $name . "</Name>
            <Description>" . $description . "</Description> 
            <CustomerName>" . $name . "</CustomerName>
            <Address>
                <HouseNumber>" . $add_number . "</HouseNumber> 
                <StreetName>" . $add_street . "</StreetName> 
                <City>" . $add_city . "</City> 
                <StateCode>" . $add_state . "</StateCode> 
                <ZipCode>" . $add_zip . "</ZipCode> 
                <AddressType>Service</AddressType> 
            </Address>
        </Site>";

        return $data;

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/sites",
            CURLOPT_POSTFIELDS => $data
        ));

        return simplexml_load_string(curl_exec($this->_curl));
    }

    public function get_site_list() {
        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/sites",
            CURLOPT_POSTFIELDS => null
        ));

        return simplexml_load_string(curl_exec($this->_curl));
    }

    public function create_peer($name, $description, $ip) {
        $data = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
        <SipPeer>
            <PeerName>" . $name . "</PeerName>
            <Description>" . $description . "</Description>
            <IsDefaultPeer>true</IsDefaultPeer>
            <VoiceHostGroups>
                <VoiceHostGroup>
                    <Host>" . $ip . "</Host>
                </VoiceHostGroup>
            </VoiceHostGroups>
        </SipPeer>";

        //return $data;

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/sites/" . $this->_settings->site_id . "/sippeers",
            CURLOPT_POSTFIELDS => $data
        ));

        return curl_exec($this->_curl);
    }

    public function get_peer_list() {
        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/sites/" . $this->_settings->site_id . "/sippeers",
            CURLOPT_POSTFIELDS => null
        ));

        return curl_exec($this->_curl);
    }

    public function order($numbers) {
        $data = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
        <Order>
            <Name>Available Telephone Number order</Name>
            <SiteId>" . $this->_settings->site_id . "</SiteId>
            <ExistingTelephoneNumberOrderType>
                <TelephoneNumberList>";

        foreach ($numbers as $number) {
            $data .= "<TelephoneNumber>" . $number . "</TelephoneNumber>";
        }

        $data .= "</TelephoneNumberList>
            </ExistingTelephoneNumberOrderType>
        </Order>";

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/orders",
            CURLOPT_POSTFIELDS => $data
        ));

        return curl_exec($this->_curl);
    }

    public function order_status($order_id) {
        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/orders/" . $order_id,
            CURLOPT_POSTFIELDS => null
        ));

        return curl_exec($this->_curl);
    }

    public function get_number_status($number) {
        echo $this->_settings->api_url . "tns/" . $number;

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_URL => $this->_settings->api_url . "tns/" . $number,
            CURLOPT_POSTFIELDS => null
        ));

        return curl_exec($this->_curl);
    }
}

 ?>