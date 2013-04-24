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
        $this->_init_mysql();
    }

    function __destruct() {
        //$this->_db = null;
    }

    private function _init_mysql() {
        // Set the DSN (the string that determines what driver to user and how)
        $dsn = "mysql:host=" . $this->_settings->database->database_host . ";dbname=" . $this->_settings->database->database_name . ";charset=" . $this->_settings->database->database_charset;        
        // Set the driver parameters
        $drvr_params = array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        // Creating a connexion

        try {
            $this->_db = new PDO($dsn, $this->_settings->database->database_user, $this->_settings->database->database_password, $drvr_params);
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
        }
    }
}

?>