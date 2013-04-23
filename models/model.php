<?php 

/**
 * Abstract model class
 * @author Francis Genet
 * @package Number_manager
 */
abstract class models_model {
    // Class attribute
    protected $_db;
    protected $_provider;
    protected $_provider_settings;
    protected $_database_settings;

    function __construct($provider = null) {
        $this->_provider = $provider;

        $general_settings = helper_settings::get_instance();
        if ($this->_provider)
            $this->_provider_settings = $general_settings->providers->{ENVIRONMENT}->$provider;
        $this->_database_settings = $general_settings->database;

        $this->_init_mysql();
    }

    function __destruct() {
        //$this->_db = null;
    }

    private function _init_mysql() {
        // Set the DSN (the string that determines what driver to user and how)
        $dsn = "mysql:host=" . $this->_database_settings->database_host . ";dbname=" . $this->_database_settings->database_name . ";charset=" . $this->_database_settings->database_charset;        
        // Set the driver parameters
        $drvr_params = array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        // Creating a connexion

        try {
            $this->_db = new PDO($dsn, $this->_database_settings->database_user, $this->_database_settings->database_password, $drvr_params);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}

 ?>