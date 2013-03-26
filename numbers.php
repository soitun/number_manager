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

    function __construct() {
        $this->_load_settings();
        $this->_init_curl();
    }

    function __destruct() {
        curl_close($this->_curl);
    }

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

    private function _load_settings() {
        $objSettings = new Settings;
        return $this->_settings = $objSettings->get_settings();
    }

    private function _get_authorization_string() {
        $credentials = $this->_settings->username . ":" . $this->_settings->password;
        return base64_encode($credentials);
    }

    /**
     * will return an object with the info on the phone registration
     *
     * @url POST /site/{site_name}
     */
    function create_site($site_name, $request_data) {
        $data = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?> 
        <Site>
            <Name>" . $site_name . "</Name>
            <Description>2600hz main site</Description> 
            <CustomerName>2600hz</CustomerName>
            <Address>
                <HouseNumber>116</HouseNumber> 
                <StreetName>Natoma street</StreetName> 
                <City>San Francisco</City> 
                <StateCode>CA</StateCode> 
                <ZipCode>94105</ZipCode> 
                <AddressType>Office</AddressType> 
            </Address>
        </Site>";

        curl_setopt_array($this->_curl, array(
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_URL => $this->_settings->api_url . "accounts/" . $this->_settings->account_id . "/sites",
            CURLOPT_POSTFIELDS => $data
        ));

        $response = curl_exec($this->_curl);
        return $response;
    }
}

 ?>