<?php 

/**
 * Numbers management class
 *
 * @author Francis Genet
 * @package Bandwidth-manager
 * @version 1.0
 */

class Numbers {
    private $_curl = null;
    private $_settings = null;
    private $_provider_list = null;

    function __construct() {
        $this->_settings = Settings::get_instance();
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

    /**
     * /city OPTIONS request
     *
     * @url OPTIONS /
     * @url OPTIONS /order
     * @url OPTIONS /search
     * @url OPTIONS /status
     */
    function options() {
        return;
    }

    /**
     * Site creation API
     *
     * @url POST /site
     */
    function create_site($request_data) {
        $data = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?> 
        <Site>
            <Name>" . $request_data['site_name'] . "</Name>
            <Description>2600hz main site</Description> 
            <CustomerName>2600hz</CustomerName>
            <Address>
                <HouseNumber>116</HouseNumber> 
                <StreetName>Natoma street</StreetName> 
                <City>San Francisco</City> 
                <StateCode>CA</StateCode> 
                <ZipCode>94105</ZipCode> 
                <AddressType>Service</AddressType> 
            </Address>
        </Site>";

        return $data;

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/sites",
            CURLOPT_POSTFIELDS => $data
        ));

        $response = curl_exec($this->_curl);
        return $response;
    }

    /**
     * Get sites on an account
     *
     * @url GET /site
     */
    function get_site($request_data) {
        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/sites",
            CURLOPT_POSTFIELDS => null
        ));

        $response = curl_exec($this->_curl);
        return $response;
    }

    /**
     * Peer creation API
     *
     * @url POST /peer
     */
    function create_peer($request_data) {
        $data = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
        <SipPeer>
            <PeerName>2600hz</PeerName>
            <Description>2600hz SIP peer</Description>
            <IsDefaultPeer>true</IsDefaultPeer>
            <VoiceHostGroups>
                <VoiceHostGroup>
                    <Host>" . $request_data['ip'] . "</Host>
                </VoiceHostGroup>
            </VoiceHostGroups>
        </SipPeer>";

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/sites/" . $this->_settings->site_id . "/sippeers",
            CURLOPT_POSTFIELDS => $data
        ));

        $response = curl_exec($this->_curl);
        return $response;
    }

    /**
     * Peer creation API
     *
     * @url PUT /peer
     */
    function update_peer($request_data) {
        $data = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
        <SipPeer>
            <PeerName>Raleigh</PeerName>
            <Description>Raleigh SIP Gateway</Description>
            <IsDefaultPeer>false</IsDefaultPeer>
            <VoiceHostGroups>
                <VoiceHostGroup>
                    <Host>" . $request_data['ip'] . "</Host>
                </VoiceHostGroup>
            </VoiceHostGroups>
        </SipPeer>";

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/sites/" . $this->_settings->site_id . "/sippeers/1785",
            CURLOPT_POSTFIELDS => $data
        ));

        $response = curl_exec($this->_curl);
        return $response;
    }

    /**
     * Get sites on an account
     *
     * @url GET /peer
     */
    function get_peer($request_data) {
        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/sites/" . $this->_settings->site_id . "/sippeers",
            CURLOPT_POSTFIELDS => null
        ));

        $response = curl_exec($this->_curl);
        return $response;
    }

    /**
     * will return an object with a city list
     *
     * @url GET /search
     */
    function test_search($request_data) {
        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/availableNumbers?&state=CA",
            CURLOPT_POSTFIELDS => null
        ));

        $response = curl_exec($this->_curl);
        return $response;
    }

    /*function search($request_data) {
        $pattern = $request_data['pattern'];
        isset($request_data['contiguous_number']) ? $contiguous = $request_data['contiguous_number'] : $contiguous = null;

        foreach (Utils::get_provider_list() as $provider) {
            $provider->search($pattern, $contiguous);
        }

        return array("status" => "success (Just kidding)");
    }*/

    /**
     * will return an object with a city list
     *
     * @url POST /order
     */
    function order($request_data) {
        return array("status" => "success (Just kidding)");
    }

    /**
     * will return an object with a city list
     *
     * @url GET /status
     */
    function status($request_data) {
        return array("status" => "success (Just kidding)");
    }
}

 ?>