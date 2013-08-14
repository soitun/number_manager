<?php

/**
 * Number model
 * @author Manu
 * @package Number_manager
 */

class models_location extends models_model {
    private $_table_name;
    private $_npanxx;
    private $_company;
    private $_state;
    private $_city;
    private $_county;
    private $_zipcode;
    private $_rate_center;

    // === Setter ===

    public function set_npanxx($npanxx) {
        $this->_npanxx = $npanxx;
    }

    public function set_company($company) {
        $this->_company = $company;
    }

    public function set_city($city) {
        $this->_city = $city;
    }

    public function set_state($state) {
        $this->_state = $state;
    }

    public function set_zipcode($zipcode) {
        $this->_zipcode = $zipcode;
    }

    public function set_county($county) {
        $this->_county = $county;
    }

    public function set_rate_center($rate_center) {
        $this->_rate_center = $rate_center;
    }

    // === Getter ===

    public function get_npanxx() {
        return $this->_npanxx;
    }

    public function get_company() {
        return $this->_company;
    }

    public function get_city() {
        return $this->_city;
    }

    public function get_state() {
        return $this->_state;
    }

    public function get_zipcode() {
        return $this->_zipcode;
    }

    public function get_county() {
        return $this->_county;
    }

    function __construct($country = null, $area_code = null) {
        parent::__construct();
        if($country && $area_code){
            $this->_table_name = 'location' . '_' . $country . '_' . $area_code;
        }
    }

    public function create_table($country, $area_code) {
        echo "Creating table for $area_code ($country)...\n";
        $this->_table_name = 'location' . '_' . $country . '_' . $area_code;
        echo "The table name will be: " . $this->_table_name . "\n";
        try{
            $query = "CREATE TABLE IF NOT EXISTS `". $this->_table_name . "` (
                `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
                `npanxx` int(7) unsigned NOT NULL,
                `company` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                `state` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
                `city` varchar(100) COLLATE utf8_unicode_ci,
                `zipcode` int(7) unsigned NOT NULL,
                `county` varchar(50) COLLATE utf8_unicode_ci,
                `rate_center` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `npanxx` (`npanxx`)
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
            $query = "INSERT INTO `" . $this->_table_name . "` (`npanxx`, `company`, `state`, `city`, `zipcode`, `county`, `rate_center`) VALUES(?, ?, ?, ?, ?, ?, ?)"; 
            $stmt = $this->_db->prepare($query);
            $stmt->execute(array($this->_npanxx, $this->_company, $this->_state, $this->_city, $this->_zipcode, $this->_county, $this->_rate_center));
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }

}