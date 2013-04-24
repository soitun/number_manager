<?php 

/**
 * Number model
 * @author Francis Genet
 * @package Number_manager_api
 */
class models_numbers extends models_model{
    private $_id;
    private $_number;
    private $_last_update;
    private $_city;
    private $_state;

    // $number must be like 
    function __construct($number = null, $country = null) {
        parent::__construct();

        if ($number && $country) {
            // Too static
            $db_name = $country . '_' . substr($number, 1, 3);
            $query = "SELECT * FROM `" . . "` WHERE `number` = ?";
            $stmt = $this->_db->prepare($query);
            $stmt->execute(array($number));

            if ($stmt->rowCount()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->_id = $result['id'];
                $this->_number = $result['number'];
                $this->_last_update = $result['last_update'];
                $this->_city = $result['city'];
                $this->_state = $result['state'];

                return true;
            } else 
                return false;
        }
    }

    public function search_by_number($area_code, $country, $limit = null, $offset = null) {
        $like = '1' . $area_code . '%';
        $db_name = $country . '_' . $area_code;

        if (!$limit && !$offset)
            $query = "SELECT `number`, `last_update`, `city`, `state` FROM `" . $db_name . "` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT 10";
        elseif ($limit && $offset)
            $query = "SELECT `number`, `last_update`, `city`, `state` FROM `" . $db_name . "` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT " . $offset . ", " . $limit;
        elseif ($limit && !$offset)
            $query = "SELECT `number`, `last_update`, `city`, `state` FROM `" . $db_name . "` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT " . $limit;
        else
            return false;

        $stmt = $this->_db->prepare($query);
        $stmt->execute(array($like));

        if ($stmt->rowCount())
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        else
            return false;
    }
}

 ?>