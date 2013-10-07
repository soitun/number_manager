<?php 

/**
 * Number model
 * @author Francis Genet
 * @package Number_manager_api
 */
 class models_metadata extends models_model {
    private $_number;
    private $_npanxx;
    private $_company;
    private $_state;
    private $_city;
    private $_zipcode;
    private $_county;
    private $_rate_center;
    private $_country;

    public function get_company() {
        return $this->_company;
    }

    public function get_state() {
        return $this->_state;
    }

    public function get_city() {
        return $this->_city;
    }

    public function get_zipcode() {
        return $this->_zipcode;
    }

    public function get_county() {
        return $this->_county;
    }

    public function get_rate_center() {
        return $this->_rate_center;
    }

    public function get_country() {
        return $this->_country;
    }

    function __construct() {
        parent::__construct();
    }

    public function get_metadata($number, $country) {
        // Getting rid of the potential '+'
        $number = str_replace('+', '', $number);
        // Then we get rid of the potential '1' 
        // (or other, could be 33, let's just take the 10 last numbers)
        if (strlen($number > 10))
            $number = substr($number, -10);

        $npa = substr($number, 0, 3);
        $npanxx = substr($number, 0, 6);

        try {
            // Choosing the right table
            $table_name = 'location_' . $country . '_' . $npa;
            $query = "SELECT * FROM `" . $table_name . "` WHERE `npanxx` = ?";

            $stmt = $this->_db->prepare($query);
            $stmt->execute(array($npanxx));

            if ($stmt->rowCount()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->_number = $number;
                $this->_country = $country;
                $this->_npanxx = $npanxx;
                $this->_company = $result[0]['company'];
                $this->_state = $result[0]['state'];
                $this->_city = $result[0]['city'];
                $this->_zipcode = $result[0]['zipcode'];
                $this->_county = $result[0]['county'];
                $this->_rate_center = $result[0]['rate_center'];
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function to_array() {
        $return = array();
        $return['country'] = $this->_country;
        $return['npanxx'] = $this->_npanxx;
        $return['company'] = $this->_company;
        $return['state'] = $this->_state;
        $return['city'] = $this->_city;
        $return['zipcode'] = $this->_zipcode;
        $return['county'] = $this->_county;
        $return['rate_center'] = $this->_rate_center;

        return $return;
    }
 }