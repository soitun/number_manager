<?php 

/**
 * Utils APIs class
 *
 * @author Francis Genet
 * @package Bandwidth-manager
 * @version 1.0
 */

class Utilities {
    private $_db = null;
    private $_settings = null;

    function __construct() {
        $this->_settings = Settings::get_instance();
    }

    function __destruct() {
        
    }

    /**
     * /city OPTIONS request
     *
     * @url OPTIONS /city
     */
    function options() {
        return;
    }

    /**
     * will return an object with a city list
     *
     * @url GET /city
     */
    function get_city_list($request_data = null) {
        // Temp return value
        return array(
            "data" => array(
                "San Francisco" => array(
                    "area_code" => array("415")
                ),
                "San Francisco 2" => array(
                    "area_code" => array("650")
                ),
                "Averell city" => array(
                    "area_code" => array("666", "777")
                )
            )
        );
    }

    /**
     * will return an object with a city list
     *
     * @url GET /supportedcountry
     */
    function get_supported_country() {
        return;
    }
}
    
 ?>