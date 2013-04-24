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

        $result = array();
        foreach ($country_list as $row) {
            $row['local'] = (bool)$row['local'];
            $row['vanity'] = (bool)$row['vanity'];
            $explode_toll_free = explode(' ', $row['toll_free']);

            $toll_free = array();
            foreach ($explode_toll_free as $key) {
                $key = (int)$key;
                array_push($toll_free, $key);
            }

            $row['toll_free'] = $toll_free;
            $row['prefix'] = (int)$row['prefix'];

            array_push($result, $row);
        }

        if($country_list) {
            return array("status" => "success", "data" => $result);
        } else {
            return array("status" => "error", "data" => array());
        }
    }
}

 ?>