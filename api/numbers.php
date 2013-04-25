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
                return array("status" => "success", "data" => array($result));
            else
                return array("status" => "error", "data" => array("message" => "Nothing found"));
        } else {
            return array("status" => "error", "data" => array("message" => "Bad request"));
        }
    }

    /**
     * Make a number(s) order
     *
     * @url GET /{country}/order
     */
    function order($request_data, $country) {
        $bandwidth = new models_bandwidth();
        //print_r($bandwidth->get_site_list());
        //print_r($bandwidth->get_peer_list());
        //print_r($bandwidth->create_peer("2600hz", "2600hz SIP peer", "184.106.157.174"));
        //print_r($bandwidth->order(array("9193752369", "9193752720")));
        //return $bandwidth->order_status("738adbaa-0032-4a04-a296-51fa9374717b");
    }
}

 ?>