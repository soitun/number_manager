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

    /*private function _check_status_bulk($number_list, &$failed_numbers_arr_obj, &$number_arr_obj) {
        foreach ($number_list as $number) {
            $number_obj = new models_number($country_obj->get_prefix() . $number, $country);
            $provider = $number_obj->get_provider();

            // I think that I need to that to make the provider class name variable
            // new providers_{$provider}_sdk does not seems to work
            $model_name = "providers_" . $provider . "_provider";
            $provider_obj = new $model_name();

            $check_result = $provider_obj->check_status($number, $country);
            
            if (!$check_result) 
                $failed_numbers_arr_obj[] = $number_obj;
            else $number_arr_obj[] = $number_obj;
        }
    }*/

    private function _order_bulk($request_data, $country, &$failed_numbers_arr) {
        foreach ($request_data['data']['numbers'] as $number) {
            $number = str_replace('+', '', $number);
            $number_obj = new models_number($number, $country);
            $identifier = $number_obj->get_number_identifier();

            $provider = $number_obj->get_provider();
            $model_name = "providers_" . $provider . "_provider";
            $provider_obj = new $model_name();

            $order_result = $provider_obj->order($request_data, $identifier);

            // The numbers should be ordered first.
            if(!$order_result) {
                return array("status" => "error", "data" => array("message" => "the $number ($identifier) was not available anymore"));
            } else {
                $number_obj->delete();
                return array("status" => "success", "data" => $order_result);
            }
        }
    }

    /**
     * Options requests
     *
     * @url OPTIONS /{country}/search
     * @url OPTIONS /{country}/search_tollfree
     * @url OPTIONS /{country}/block_search
     * @url OPTIONS /{country}/order
     * @url OPTIONS /{country}/status
     * @url OPTIONS /{country}/_block_status
     * @url OPTIONS /{country}/meta
     */
    function options() {
        return;
    }

    /**
     * Do a research by states
     *
     * @url GET /{country}/state/search
     */
    function search_by_states($request_data, $country){
        $number_obj = new models_number();
        $number_state_list = $number_obj->search_by_states($request_data['states'], $country);

        return $number_state_list;
    }

    /**
     * Do a research by providers
     *
     * @url GET /providers/search
     */    
    function search_by_providers($request_data) {
        $number_obj = new models_number();
        $number_list = $number_obj->search_by_provider($request_data['provider']);
        return $number_list;
    }

    /**
     * Do a research by area code or npanxx
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
     * Do a research by area code, npanxx or city
     *
     * @url GET /{country}/search_tollfree
     */
    function search_tollfree($request_data, $country) {
        $pattern = $request_data['pattern'];
        $limit = isset($request_data['limit']) ? $request_data['limit'] : null;
        $offset = isset($request_data['offset']) ? $request_data['offset'] : null;

        $numbers = new models_tollfree();
        $result = $numbers->search_by_number($pattern, $country, $limit, $offset);
        if ($result)
            return array("status" => "success", "data" => $result);
        else
            return array("status" => "error", "data" => array("message" => "Nothing found"));
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
        $failed_numbers_arr = array();

        return $this->_order_bulk($request_data, $country, $failed_numbers_arr);
    }

    /**
     * Check number(s) status
     *
     * @url POST /{country}/status
     */
    function status($request_data, $country) {
        $country_obj = new models_country($country);
        $result = array();

        foreach ($request_data['data'] as $number) {
            $number_obj = new models_number($country_obj->get_prefix() . $number, $country);
            $provider = $number_obj->get_provider();

            $model_name = "providers_" . $provider . "_provider";
            $provider_obj = new $model_name();

            $check_result = $provider_obj->check_status($number, $country);
            if (!$check_result) {
                $tmp = array("number" => $number);
                $tmp['status'] = "error";
            } else {
                $tmp = array("number" => $number);
                $tmp['status'] = "success";
            }

            array_push($result, $tmp);
        }

        return array("status" => "success", "data" => $result);
    }

    /**
     * Check number(s) status
     *
     * @url POST /meta
     */
    function search_meta($request_data) {
        $return = array("data" => array());
        $numbers = $request_data['data'];
        $countryObj = new models_country();

        foreach ($numbers as $number) {
            $country =  $countryObj->get_country($number);
            $numberObj = new models_number(str_replace('+', '', $number), $country);
            $return['data'][$number] = $numberObj->get_metaObj()->to_array();
        }

        return $return;
    }

    /**
     * Check number(s) status
     *
     * @url GET /{country}/_block_status
     */
    function block_status($request_data, $country) {
        /*$bandwidth = new providers_bandwidth_sdk();

        foreach ($request_data['data'] as $number) {
            return $bandwidth->get_number_status($number);
        }*/
    }
}

 ?>