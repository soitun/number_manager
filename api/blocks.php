<?php 

/**
 * Blocks APIs
 *
 * @author Francis Genet
 * @package Number_manager_api
 * @version 1.0
 */

class Blocks {
    function __construct() {

    }

    // Yep...
    function options() {
        return;
    }

    /**
     * Do a block research
     *
     * @url GET /{country}/search
     */
    function search($request_data, $country) {
        $pattern = $request_data['pattern'];
        $limit = isset($request_data['limit']) ? $request_data['limit'] : null;
        $offset = isset($request_data['offset']) ? $request_data['offset'] : null;

        if (isset($request_data['size']) && $request_data['size'] >= 1) {
            $block = new models_block();
            $result = $block->get_blocks($pattern, $request_data['size'], $country, $limit, $offset);
            if ($result)
                return array("data" => array("status" => "success", "result" => $result));
            else
                return array("data" => array("status" => "error", "message" => "Nothing found"));
        } else {
            return array("data" => array("status" => "error", "message" => "Bad request"));
        }
    }
}

 ?>