<?php 

/**
 * Basic model
 * @author Francis Genet
 * @package Number_manager_api
 */
class models_model {
    // Class attribute
    protected $_db;
    protected $_settings;

    function __construct() {
        $this->_settings = helper_settings::get_instance();

        if (!$this->_init_mysql())
            return false;
    }

    function __destruct() {
        //$this->_db = null;
    }

    protected function get_db_name($pattern, $country) {
        $country_obj = new models_country($country);
        return $country . '_' . substr($pattern, strlen($country_obj->get_prefix()), 3);
    }

    private function _init_mysql() {
        // Set the DSN (the string that determines what driver to user and how)
        $dsn = "mysql:host=" . $this->_settings->database->database_host . ";dbname=" . $this->_settings->database->database_name . ";charset=" . $this->_settings->database->database_charset;
        // Set the driver parameters
        $drvr_params = array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        // Creating a connexion
        try {
            $this->_db = new PDO($dsn, $this->_settings->database->database_user, $this->_settings->database->database_password, $drvr_params);
            return true;
        } catch (PDOException $e) {
            //echo $e->getMessage() . "\n";
            return false;
        }
    }
}

?>