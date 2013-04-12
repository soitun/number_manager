<?php

/**
 * Number model
 * @author Francis Genet
 * @package Number_manager
 */
class models_number extends models_model {
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

    function __construct($provider) {
        parent::__construct($provider);
    }

    function __destruct() {
        parent::__destruct();
    }

    // Adding number in DB
    public function insert() {
        try {
            $stmt = $this->_db->prepare("INSERT INTO `numbers`(`number`, `provider`, `cache_update`, `city`, `state`) VALUES(?, ?, now(), ?, ?)");
            $stmt->execute(array($this->_number, $this->_provider, $this->_city, $this->_state));
        } catch (PDOException $e) {
            return true;
        }

        return true;
    }
}

 ?>