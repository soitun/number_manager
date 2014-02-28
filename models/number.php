<?php

/**
 * Number model
 * @author Francis Genet
 * @package Number_manager
 */
class models_number extends models_model {
    private $_id;
    private $_number;
    private $_last_update;
    private $_city;
    private $_state;
    private $_number_identifier;
    private $_db_name;

    // === Setter ===

    public function set_number($number) {
        $this->_number = $number;
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

    public function set_number_identifier($id) {
        $this->_number_identifier = $id;
    }

    public function set_provider($provider) {
        $this->_provider = $provider;
    }

    public function create_db($db_name) {
        $this->_db_name = $db_name;

        try {
            $query = "CREATE TABLE IF NOT EXISTS `" . $db_name . "` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `number` bigint(20) unsigned NOT NULL,
              `provider` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
              `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `city` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
              `state` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
              `number_identifier` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `number` (`number`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";

            $stmt = $this->_db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function set_db_name($db_name) {
        $this->_db_name = $db_name;
    }

    // === Getter ===

    public function get_number() {
        return $this->_number;
    }

    public function get_last_update() {
        return $this->_last_update;
    }

    public function get_city() {
        return $this->_city;
    }

    public function get_state() {
        return $this->_state;
    }

    public function get_provider() {
        return $this->_provider;
    }

    public function get_number_indentifier() {
        return $this->_number_identifier;
    }

    // ==============

    function __construct($provider = null) {
        parent::__construct($provider);
    }

    function __destruct() {
        parent::__destruct();
    }

    public function start_transaction() {
        $this->_db->beginTransaction();
    }

    public function commit() {
        $this->_db->commit();
    }

    public function rollback() {
        $this->_db->rollBack();
    }

    // Adding number in DB
    public function insert() {
        try {
            $stmt = $this->_db->prepare("INSERT INTO `" . $this->_db_name . "` (`number`, `provider`, `city`, `state`, `number_identifier`) VALUES(?, ?, ?, ?, ?)");
            $stmt->execute(array($this->_number, $this->_provider, $this->_city, $this->_state, $this->_number_identifier));
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    public function delete_like_number($number) {
        $like = $number . '%';

        try {
            $stmt = $this->_db->prepare("DELETE FROM `" . $this->_db_name . "` WHERE `number` LIKE ? AND `provider` = ?");
            $stmt->execute(array($like, $this->_provider));
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    public function delete_like_city() {
        try {
            $stmt = $this->_db->prepare("DELETE FROM `" . $this->_db_name . "` WHERE `city` = ? AND `provider` = ?");
            $stmt->execute(array($this->_city, $this->_provider));
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    public function truncate() {
        try {
            $query = "TRUNCATE TABLE " . $this->_db_name;
            $stmt = $this->_db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }
    }
}

 ?>