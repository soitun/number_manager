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
                `npa` varchar(255) NOT NULL,
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
            $query = "SELECT * FROM `" . $this->_table_name . "` WHERE `city` = ? and `state` = ?";
            $stmt = $this->_db->prepare($query);
            $stmt->execute(array($this->_city, $this->_state));

            if (!$stmt->rowCount()) {
                echo "No result, will insert first data\n";
                // We then need to add it
                $query = "INSERT INTO `" . $this->_table_name . "` (`npa`, `state`, `city`) VALUES(?, ?, ?)"; 
                $stmt = $this->_db->prepare($query);
                $stmt->execute(array($this->_npa, $this->_state, $this->_city));
            } else {
                echo "City already exist, will update\n";
                // Otherwise it will be an update
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $expl_npa = explode(',', $result[0]['npa']);
                echo "Current object npa: " . $this->_npa . "\n";
                echo "Exploded npa: \n";
                print_r($expl_npa);

                // If this npa is already in the list
                if (!in_array($this->_npa, $expl_npa)) {
                    echo "Adding npa to " . $this->_city . "\n";
                    $npa = $result[0]['npa'] . "," . $this->_npa;
                    echo "npa that will be added: $npa \n";
                    $query = "UPDATE `" . $this->_table_name . "` SET `npa` = ? WHERE `id` = ?";
                    $stmt = $this->_db->prepare($query);
                    $stmt->execute(array($npa, $result[0]['id']));
                }
            }

        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
        return true;
    }

}