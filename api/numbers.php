<?php 

/**
 * Numbers APIs
 *
 * @author Francis Genet
 * @package Number_manager_api
 * @version 1.0
 */

class Numbers {
    function __construct() {

    }

    // Yep...
    function options() {
        return;
    }

    /**
     * Do a research by area code, npanxx or city
     *
     * @url GET /{country}/search
     */
    function search($request_data, $country) {
        $pattern = $request_data['pattern'];
        $limit = isset($request_data['limit']) ? $request_data['limit'] : null;
        $offset = isset($request_data['offset']) ? $request_data['offset'] : null;

        $numbers = new models_number();
        if (is_numeric($pattern)){
            $result = $numbers->search_by_number($pattern, $country, $limit, $offset);
            if ($result)
                return array("status" => "success", "data" => $result);
            else
                return array("status" => "error", "data" => array("message" => "Nothing found"));
        } else {
            return "city";
        }
    }

    /**
     * Do a block research
     *
     * @url GET /{country}/block_search
     */
    function block_search($request_data, $country) {
        $pattern = $request_data['pattern'];
        $size = $request_data['size'];
        $limit = isset($request_data['limit']) ? $request_data['limit'] : null;
        $offset = isset($request_data['offset']) ? $request_data['offset'] : null;

        if ($size) {
            $block = new models_block();
            $result = $block->get_blocks($pattern, $size, $country, $limit, $offset);
            if ($result)
                return array("status" => "success", "data" => $result);
            else
                return array("status" => "error", "data" => array("message" => "Nothing found"));
        } else {
            return array("status" => "error", "data" => array("message" => "Bad request"));
        }
    }

    /**
     * Make a number(s) order
     *
     * @url PUT /{country}/order
     */
    function order($request_data, $country) {
        $bandwidth = new models_bandwidth();
        $country_obj = new models_country($country);

        // The numbers should be ordered first.
        if (!$bandwidth->order($request_data['data']))
            return array("status" => "error", "data" => array("message" => "Ay least one number was not available anymore"));

        // This will delete the numbers.
        foreach ($request_data['data'] as $number) {
            $number_obj = new models_number($country_obj->get_prefix() . $number, $country);
            $number_obj->delete();
        }
    }

    /**
     * Check number(s) status
     *
     * @url GET /{country}/status
     */
    function status($request_data, $country) {
        $country_obj = new models_country($country);
        $result = array();

        foreach ($request_data['data'] as $number) {
            $pref_number = $country_obj->get_prefix() . $number;
            $tmp = array("number" => $number);

            $number_obj = new models_number($pref_number, $country);
            if($number_obj->exist()) {
                $tmp['status'] = "success";
            } else {
                $tmp['status'] = "error";
            }

            array_push($result, $tmp);
        }

        return array("status" => "success", "data" => $result);
    }

    /**
     * Check number(s) status
     *
     * @url GET /{country}/block_status
     */
    function block_status($request_data, $country) {
        
    }
}

 ?>