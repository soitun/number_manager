<?php 

/**
 * Block model
 * @author Francis Genet
 * @package Number_manager
 */
class models_block extends models_model {
    private $_id;
    private $_size;
    private $_start_number;
    private $_end_number;

    function __construct() {
        parent::__construct();
    }

    public function get_blocks($area_code, $size, $country, $limit = null, $offset = null) {
        $like = '1' . $area_code . '%';

        if (!$limit && !$offset)
            $query = "SELECT * FROM `blocks` WHERE `size` >= ? AND `start_number` LIKE ? ORDER BY `start_number` ASC LIMIT 10";
        elseif ($limit && $offset)
            $query = "SELECT * FROM `blocks` WHERE `size` >= ? AND `start_number` LIKE ? ORDER BY `start_number` ASC LIMIT " . $offset . ", " . $limit;
        elseif ($limit && !$offset)
            $query = "SELECT * FROM `blocks` WHERE `size` >= ? AND `start_number` LIKE ? ORDER BY `start_number` ASC LIMIT " . $limit;
        else
            return false;

        $stmt = $this->_db->prepare($query);
        $stmt->execute(array($size, $like));

        if ($stmt->rowCount()) {
            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $return_array = array();
            foreach ($fetch as $item) {
                $item['size'] = $size;
                $item['end_number'] = (string)($item['start_number'] + ($size - 1));
                unset($item['id']);
                unset($item['provider']);
                array_push($return_array, $item);
            }

            return $return_array;
        } else
            return false;
    }
}
    
 ?>