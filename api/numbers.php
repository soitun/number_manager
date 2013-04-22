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
            $result = $numbers->search_by_area_code($pattern, $country, $limit, $offset);
            if ($result)
                return array("data" => $result);
            else
                return array("data" => array("status" => "error", "message" => "Nothing found"));
        } else {
            return "city";
        }
    }

    /**
     * This will allow the user to get the default settings for an account and for a phone 
     *
     * @url GET /{country}/status
     */
    function status($request_data, $country) {
        if (!isset($request_data['number']))
            throw new RestException(400, "This request need a 'number' parameter");

        $number = new models_number();
        $result = $number->search_by_number($request_data['number']);

        if ($result) {
            return array("data" => array("status" => "success", "message" => "Number available"));
        } else {
            return array("data" => array("status" => "error", "message" => "Number not available"));
        }
    }
}

 ?>