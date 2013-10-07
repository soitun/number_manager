<?php

/**
 * Number model
 * @author Manu
 * @package Number_manager
 */

class models_citymap extends models_model {
    private $_table_name;
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

    public function create_table($country = null) {
        echo "Creating table city_map...\n";
        try{
            $query = "CREATE TABLE IF NOT EXISTS `". $this->_table_name . "` (
                `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
                `npa` int(4) unsigned NOT NULL,
                `state` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
                `city` varchar(100) COLLATE utf8_unicode_ci,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
            
            $stmt = $this->_db->prepare($query);
            $stmt->execute();

            echo "Done! \n";
        }   catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function insert() {
        try {
            $query = "SELECT id FROM `" . $this->_table_name . "` WHERE `city` = ? and `npa` = ?";
            $stmt = $this->_db->prepare($query);
            $stmt->execute(array($this->_city, $this->_npa));

            if ($stmt->rowCount())
                return false;

            $query = "INSERT INTO `" . $this->_table_name . "` (`npa`, `state`, `city`) VALUES(?, ?, ?)"; 
            $stmt = $this->_db->prepare($query);
            $stmt->execute(array($this->_npa, $this->_state, $this->_city));
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
        return true;
    }

}