<?php 

/**
 * Number model
 * @author Francis Genet
 * @package Number_manager_api
 */
class models_numbers extends models_model {
    private $_id;
    private $_number;
    private $_last_update;
    private $_city;
    private $_state;
    private $_exist;
    private $_provider;
    private $_db_name;
    private $_number_identifier;
    private $_list_numbers;
    private $_metaObj;

    public function get_number() {
        return $this->_number;
    }

    public function get_provider() {
        return $this->_provider;
    }

    public function get_number_identifier() {
        return $this->_number_identifier;
    }

    public function get_metaObj() {
        return $this->_metaObj;
    }

    // $number must be like 
    function __construct($number = null, $country = null) {
        parent::__construct();
        $this->_exist = false;

        if ($number && $country) {
            try {
                $this->_metaObj = new models_metadata();

                $this->_db_name = $this->get_db_name($number, $country);
                $query = "SELECT * FROM `" . $this->_db_name . "` WHERE `number` = ?";
                $stmt = $this->_db->prepare($query);
                $stmt->execute(array($number));

                if ($stmt->rowCount()) {
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->_id = $result[0]['id'];
                    $this->_number = $result[0]['number'];
                    $this->_provider = $result[0]['provider'];
                    $this->_last_update = $result[0]['last_update'];
                    $this->_city = $result[0]['city'];
                    $this->_state = $result[0]['state'];
                    $this->_number_identifier = $result[0]['number_identifier'];
                    $this->_exist = true;

                    // Retrieving metadata
                    $this->_metaObj->get_metadata($number, $country);
                }
            } catch(PDOException $e) {
                echo $e->getMessage();
                //$this->_log->logFatal($e);
                exit('{"status": "error", "data": {}}');
            } 
        }
    }

    public function search_by_number($pattern, $country, $limit = null, $offset = null) {
        // If the number is something like +14158867900
        if (strlen($pattern) == 12)
            $pattern = substr($pattern, 1);
        else // This should then be a area code like 415
            $pattern = '1' . $pattern;

        $like = $pattern . '%';
        $this->_db_name = $this->get_db_name($pattern, $country);

        if ($limit > 100)
            $limit = 100;

        if (!$limit && !$offset)
            $query = "SELECT `number`, `last_update`, `city`, `state` FROM `" . $this->_db_name . "` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT 10";
        elseif ($limit && $offset)
            $query = "SELECT `number`, `last_update`, `city`, `state` FROM `" . $this->_db_name . "` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT " . $offset . ", " . $limit;
        elseif ($limit && !$offset)
            $query = "SELECT `number`, `last_update`, `city`, `state` FROM `" . $this->_db_name . "` WHERE `number` LIKE ? ORDER BY `number` ASC LIMIT " . $limit;
        else
            return false;

        $stmt = $this->_db->prepare($query);
        $stmt->execute(array($like));

        if ($stmt->rowCount()) {
            $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $data = array();

            foreach ($raw as $number_obj) {
                $data['+' . $number_obj['number']] = $number_obj;
            }

            return $data;
        } else
            return false;
    }

    /*get numbers from states*/
    public function search_by_states($states, $country){
        $this->_list_numbers = array();
        $result = $this->_db->query("show tables");
        $rows = $result->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $row) {
            if (preg_match("#^" . $country . "_[0-9]{3}$#", $row[0])) {
                $query = "SELECT * FROM `" . $row[0] . "` WHERE `state` = ?";
                $stmt = $this->_db->prepare($query);
                $stmt->execute(array($states));
                if ($stmt->rowCount()) {
                    $number_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->_list_numbers = array_merge($this->_list_numbers, $number_result);
                }
            }       
        }       

        return $this->_list_numbers;
    }

    /* get numbers from providers */
    public function search_by_provider($provider){
        $this->_list_numbers = array();
        $result = $this->_db->query("show tables");
        $rows = $result->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $row) {
            if (preg_match("#^[a-zA-Z]{2}_[0-9]{3}$#", $row[0])) {
                $query = "SELECT * FROM `" . $row[0] . "` WHERE `provider` = ?";
                $stmt = $this->_db->prepare($query);
                $stmt->execute(array($provider));
                if ($stmt->rowCount()) {
                    $number_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->_list_numbers = array_merge($this->_list_numbers, $number_result);
                }
            }   
        }

        return $this->_list_numbers;
    }

    /*public function get_provider_from_identifier($number_identifier, $country) {
        $db_name = $this->get_db_name($pattern, $country);
        $query = "SELECT provider FROM `" . $db_name . "` WHERE `number_identifier` = ?";

        $stmt = $this->_db->prepare($query);
        $stmt->execute(array($number_identifier));

        if ($stmt->rowCount()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result[0]['provider'];
        } else return false;
    }*/

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