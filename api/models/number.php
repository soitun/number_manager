<?php 

/**
 * Number model
 * @author Francis Genet
 * @package Number_manager
 */
class models_numbers extends models_model{
    private $_id;
    private $_number;
    private $_cache_date;
    private $_last_update;
    private $_city;
    private $_state;

    // === Setter ===

    public function set_number($number) {
        $this->_number = $number;
    }

    public function set_cache_date($cache_date) {
        $this->_cache_date = $cache_date;
    }

    public function set_last_update($last_update) {
        $this->_last_update = $last_update;
    }

    public function set_city($city) {
        $this->_city = $city;
    }

    public function set_state($state) {
        $this->_state = $state;
    }

    // === Getter ===

    public function get_number($number) {
        return $this->_number;
    }

    public function get_cache_date($cache_date) {
        return $this->_cache_date;
    }

    public function get_last_update($last_update) {
        return $this->_last_update;
    }

    public function get_city($city) {
        return $this->_city;
    }

    public function get_state($state) {
        return $this->_state;
    }

    // ==============

    function __construct() {
        parent::__construct();
    }

    public function search_by_area_code($area_code, $limit = null, $offset = null) {
        $like = $area_code . '%';

        if (!$limit && !$offset)
            $query = "SELECT * FROM `numbers` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT 10";
        elseif ($limit && $offset)
            $query = "SELECT * FROM `numbers` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT " . $offset . ", " . $limit;
        elseif ($limit && !$offset)
            $query = "SELECT * FROM `numbers` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT " . $limit;
        else
            return false;

        $stmt = $this->_db->prepare($query);
        $stmt->execute(array($like));

        if ($stmt->rowCount())
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        else
            return false;
    }

    public function search_by_number($number) {
        $stmt = $this->_db->prepare("SELECT * FROM `numbers` WHERE `number`=?");
        $stmt->execute(array($number));

        if ($stmt->rowCount())
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        else
            return false;
    }
}

 ?>