<?php 

/**
 * Utilities APIs
 *
 * @author Francis Genet
 * @package Number_manager_api
 * @version 1.0
 */

class Utilities {
    function __construct() {

    }

    // Yep...
    function options() {
        return;
    }

    /**
     * Get all the supported countries and their infos
     *
     * @url GET /countries
     */
    function get_countries() {
        $country_obj = new models_country();
        $country_list = $country_obj->get_countries();

        if($country_list) {
            return array("status" => "success", "data" => $country_list);
        } else {
            return array("status" => "error", "data" => array());
        }
    }
}

 ?>