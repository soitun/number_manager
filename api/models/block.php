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

    // === Setter ===

    public function set_size($size) {
        $this->_size = $size;
    }

    public function set_start_number($start_number) {
        $this->_start_number = $start_number;
    }

    public function set_end_number($end_number) {
        $this->_end_number = $end_number;
    }

    // === Getter ===

    public function get_size($size) {
        return $this->_size;
    }

    public function get_start_number($start_number) {
        return $this->_start_number;
    }

    public function get_end_number($end_number) {
        return $this->_end_number;
    }

    // ==============

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