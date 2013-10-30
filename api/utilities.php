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

    /**
     * Options requests
     *
     * @url OPTIONS /countries
     * @url OPTIONS /location
     */
    function options() {
        return;
    }

    /**
     * Get all the supported countries and their infos
     *
     * @url POST /location
     */
    function get_location($request_data) {
        $return = array("data" => array());
        $numbers = $request_data['data'];
        $countryObj = new models_country();
        $metaObj = new models_metadata();

        foreach ($numbers as $number) {
            $country =  $countryObj->get_country($number);
            $metaObj->get_metadata($number, $country);
            $return['status'] = 'success';
            $return['data'][$number] = $metaObj->to_array();
        }

        if (!count($return['data'])) {
            $return['status'] = "error";
            $return['data']['message'] = "Could not find any data for this(those) numbers";
        }

        return $return;
    }

    /**
     * Get all the supported countries and their infos
     *
     * @url GET /{country}/city
     */
    function get_prefix_by_city($request_data, $country) {
        $return = array("status" => "", "data" => array());
        $pattern = $request_data['pattern'];
        $citymap_obj = new models_citymap($country);

        $citymap_result = $citymap_obj->get_prefix_by_cityname($pattern);
        foreach ($citymap_result as $city_info) {
            $expl_npa = explode(',', $city_info['npa']);
            $return['data'][$city_info['city']]['state'] = $city_info['state'];
            $return['data'][$city_info['city']]['prefixes'] = $expl_npa;
        }
        $return['status'] = 'success';

        return $return;
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
            // Casting to int (it is a string from the DB)
            foreach ($explode_toll_free as $key) {
                $key = (int)$key;
                array_push($toll_free, $key);
            }

            $row['toll_free'] = $toll_free;
            $row['prefix'] = (int)$row['prefix'];

            $result[$row['iso_code']] = $row;
            unset($result[$row['iso_code']]['iso_code']);
        }

        if($country_list) {
            return array("status" => "success", "data" => $result);
        } else {
            return array("status" => "error", "data" => array());
        }
    }
}

 ?>