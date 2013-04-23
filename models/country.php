<?php 

/**
 * Country model
 * @author Francis Genet
 * @package Number_manager
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

    // === Setter ===

    public function set_iso_code($iso_code) {
        $this->_iso_code = $iso_code;
    }

    public function set_local($local) {
        $this->_local = $local;
    }

    public function set_toll_free($toll_free) {
        $this->_toll_free = $toll_free;
    }

    public function set_vanity($vanity) {
        $this->_vanity = $vanity;
    }

    public function set_prefix($prefix) {
        $this->_prefix = $prefix;
    }

    public function set_flag_url($flag_url) {
        $this->_flag_url = $flag_url;
    }

    public function set_name($name) {
        $this->_name = $name;
    }

    // === Getter ===

    public function get_iso_code() {
        return $this->_iso_code;
    }

    public function get_local() {
        return $this->_local;
    }

    public function get_toll_free() {
        return $this->_toll_free;
    }

    public function get_vanity() {
        return $this->_vanity;
    }

    public function get_prefix() {
        return $this->_prefix;
    }

    public function get_flag_url() {
        return $this->_flag_url;
    }

    public function get_name() {
        return $this->_name;
    }

    // ==============

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function insert() {
        try {
            $stmt = $this->_db->prepare("INSERT INTO `countries` (`iso_code`, `local`, `toll_free`, `vanity`, `prefix`, `flag_url`, `name`) VALUES(?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(array(
                $this->_iso_code, 
                $this->_local, 
                $this->_toll_free, 
                $this->_vanity,
                $this->_prefix,
                $this->_flag_url,
                $this->_name
            ));
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }
}

 ?>