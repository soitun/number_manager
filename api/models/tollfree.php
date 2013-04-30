<?php 

/**
 * Number model
 * @author Francis Genet
 * @package Number_manager_api
 */
class models_tollfree extends models_model{
    private $_id;
    private $_number;
    private $_last_update;
    private $_exist;
    private $_db_name;

    // $number must be like 
    function __construct($number = null, $country = null) {
        parent::__construct();
        $this->_exist = false;

        if ($number && $country) {
            $this->_db_name = $this->get_db_name();
            $query = "SELECT * FROM `" . $this->_db_name . "` WHERE `number` = ?";
            $stmt = $this->_db->prepare($query);
            $stmt->execute(array($number));

            if ($stmt->rowCount()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->_id = $result[0]['id'];
                $this->_number = $result[0]['number'];
                $this->_last_update = $result[0]['last_update'];
                $this->_city = $result[0]['city'];
                $this->_state = $result[0]['state'];
                $this->_exist = true;

                return true;
            } else return false;
        } else return false;
    }

    public function search_by_number($pattern, $country, $limit = null, $offset = null) {
        $like = '1' . $pattern . '%';
        $this->_db_name = $this->get_db_name($pattern, $country);

        if ($limit > 100)
            $limit = 100;

        if (!$limit && !$offset)
            $query = "SELECT `number`, `last_update` FROM `" . $this->_db_name . "` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT 10";
        elseif ($limit && $offset)
            $query = "SELECT `number`, `last_update` FROM `" . $this->_db_name . "` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT " . $offset . ", " . $limit;
        elseif ($limit && !$offset)
            $query = "SELECT `number`, `last_update` FROM `" . $this->_db_name . "` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT " . $limit;
        else
            return false;

        $stmt = $this->_db->prepare($query);
        $stmt->execute(array($like));

        if ($stmt->rowCount())
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        else
            return false;
    }

    public function exist() {
        return $this->_exist;
    }

    public function delete($number = null) {
        if (!$number) {
            if ($this->_id) {
                $query = "DELETE FROM `" . $this->_db_name . "` WHERE `id` = ?";
                $stmt = $this->_db->prepare($query);
                $stmt->execute(array($this->_id));
            }
        } else {
            // TBI
        }
    }
}

 ?>