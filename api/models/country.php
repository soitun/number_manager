<?php 

/**
 * Country model
 * @author Francis Genet
 * @package Number_manager_api
 */
class models_country extends models_model {
    private $_id;
    private $_iso_code;
    private $_local;
    private $_toll_free;
    private $_vanity;
    private $_prefix;
    private $_flag_url;
    private $_name;

    function __construct($iso_code = null) {
        parent::__construct();

        if ($iso_code) {
            try {
                $query = "SELECT * FROM `countries` WHERE `iso_code` = ?";
                $stmt = $this->_db->prepare($query);
                $stmt->execute(array($iso_code));

                if ($stmt->rowCount()) {
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->_id = $result[0]['id'];
                    $this->_iso_code = $result[0]['iso_code'];
                    $this->_local = $result[0]['local'];
                    $this->_toll_free = explode(' ', $result[0]['toll_free']);
                    $this->_vanity = $result[0]['vanity'];
                    $this->_prefix = $result[0]['prefix'];
                    $this->_flag_url = $result[0]['flag_url'];
                    $this->_name = $result[0]['name'];

                    return true;
                } else 
                    return false;
            } catch (PDOException $e) {
                //echo $e->get_message();
                return false;
            }
        }
    }

    public function get_country($number) {
        $prefix = substr($number, 0, -10);
        $prefix = str_replace('+', '', $prefix);

        $query = "SELECT `iso_code` FROM `countries` WHERE `prefix` = ?";
        $stmt = $this->_db->prepare($query);
        $stmt->execute(array($prefix));

        if ($stmt->rowCount()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
            return $result['iso_code'];
        } else 
            return false;
    }

    function __destruct() {
        parent::__destruct();
    }

    public function get_prefix() {
        return $this->_prefix;
    }

    public function get_countries() {
        $query = "SELECT `iso_code`, `local`, `toll_free`, `vanity`, `prefix`, `flag_url`, `name` FROM `countries`";
        $stmt = $this->_db->query($query);

        if ($stmt->rowCount())
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        else
            return false;
    }
}

 ?>