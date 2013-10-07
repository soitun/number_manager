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

    private function _check_status_bulk($number_list, &$failed_numbers_arr_obj, &$number_arr_obj) {
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
    }

    private function _order_bulk($request_data, $number_arr_obj) {
        foreach ($number_arr_obj as $number_obj) {
            $number = $number_obj->get_number();
            $identifier = $number_obj->get_number_identifier();

            $provider = $number_obj->get_provider();
            $model_name = "providers_" . $provider . "_provider";
            $provider_obj = new $model_name();
            
            // The numbers should be ordered first.
            if(!$provider_obj->order($request_data, $identifier)) {
                return array("status" => "error", "data" => array("message" => "the $number ($identifier) was not available anymore"));
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
     * @url GET /block_search
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
        $number_list = $request_data['data'];
        $country_obj = new models_country($country);
        $failed_numbers_arr_obj = array();
        $number_arr_obj = array();

        // I know that the following looks like I am doing too much foreach
        // However, in this case, we need to do 3 different step.

        // First, we need to check the status for each number
        $this->_check_status_bulk($number_list, $failed_numbers_arr_obj, $number_arr_obj);        

        // If every numbers are available
        if (empty($failed_numbers_arr_obj)) {
            // Then comes the order
            $this->_order_bulk($request_data, $number_arr_obj);
            
            // Then the numbers must be deleted from the cache DB.
            foreach ($number_arr_obj as $number_obj) {
                $number_obj->delete();
            }

            return array("status" => "success", "data" => "The order is a success");
        } else {
            foreach ($failed_numbers_arr_obj as $number_obj) {
                $tmp = array("number" => $number_obj->get_number());
                $tmp['status'] = "Unavailable";
                array_push($result, $tmp);
            }

            return array("status" => "error", "data" => $result);
        }
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