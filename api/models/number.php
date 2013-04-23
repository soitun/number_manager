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

    function __construct() {
        parent::__construct();
    }

    public function search_by_area_code($area_code, $country, $limit = null, $offset = null) {
        $like = '1' . $area_code . '%';
        $db_name = $country . '_' . $area_code;

        if (!$limit && !$offset)
            $query = "SELECT * FROM `" . $db_name . "` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT 10";
        elseif ($limit && $offset)
            $query = "SELECT * FROM `" . $db_name . "` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT " . $offset . ", " . $limit;
        elseif ($limit && !$offset)
            $query = "SELECT * FROM `" . $db_name . "` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT " . $limit;
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
        // This is not good. Hard coded here!
        $db_name =  'US_' . substr($number, 1, 3);
        $stmt = $this->_db->prepare("SELECT * FROM `" . $db_name . "` WHERE `number`=?");
        $stmt->execute(array($number));

        if ($stmt->rowCount())
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        else
            return false;
    }
}

 ?>