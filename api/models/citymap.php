<?php

/**
 * Number model
 * @author Manu
 * @package Number_manager
 */

class models_citymap extends models_model {
    private $_table_name;
    private $_id;
    private $_npa;
    private $_city;
    private $_state;

    // === Setter ===

    public function set_npa($npa) {
        $this->_npa = $npa;
    }

    public function set_state($state) {
        $this->_state = $state;
    }

    public function set_city($city) {
        // A city name should look like "Cliffside Park"
        // and not "CLIFFSIDE PARK"
        $this->_city = ucwords(strtolower($city));
    }

    // === Getter ===

    public function get_npa() {
        return $this->_npa;
    }

    public function get_city() {
        return $this->_city;
    }

    public function get_state() {
        return $this->_state;
    }

    function __construct($country) {
        parent::__construct();
        $this->_table_name = 'city_map_' . $country;
    }

    public function get_prefix_by_cityname($pattern) {
        $like = $pattern . '%';
        $query = "SELECT * FROM `" . $this->_table_name . "` WHERE city LIKE ?";
        $stmt = $this->_db->prepare($query);
        $stmt->execute(array($like));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}