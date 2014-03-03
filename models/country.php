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
            $query = "CREATE TABLE IF NOT EXISTS `countries` (
                      `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
                      `iso_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
                      `local` tinyint(4) NOT NULL DEFAULT '0',
                      `toll_free` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                      `vanity` tinyint(4) NOT NULL DEFAULT '0',
                      `prefix` int(5) unsigned NOT NULL,
                      `flag_url` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
                      `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `iso_code` (`iso_code`)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
            $stmt = $this->_db->prepare($query);
            $stmt->execute();

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