<?php

/**
 * Number model
 * @author Francis Genet
 * @package Number_manager
 */
class models_number extends models_model {
    private $_id;
    private $_number;
    private $_cache_date;
    private $_last_update;
    private $_city;
    private $_state;
    private $_db_name;

    // === Setter ===

    public function set_number($number) {
        $this->_number = $number;
    }

    public function set_cache_date($cache_date) {
        $this->_cache_date = $cache_date;
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

    public function set_or_create_db($db_name) {
        $this->_db_name = $db_name;

        try {
            $query = "CREATE TABLE IF NOT EXISTS `" . $db_name . "` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `number` bigint(20) unsigned NOT NULL,
              `provider` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
              `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `city` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
              `state` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
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

    public function get_number($number) {
        return $this->_number;
    }

    public function get_cache_date($cache_date) {
        return $this->_cache_date;
    }

    public function get_last_update($last_update) {
        return $this->_last_update;
    }

    public function get_city($city) {
        return $this->_city;
    }

    public function get_state($state) {
        return $this->_state;
    }

    // ==============

    function __construct($provider) {
        parent::__construct($provider);
    }

    function __destruct() {
        parent::__destruct();
    }

    public function start_transaction() {
        echo "start_transaction \n";
        $this->_db->beginTransaction();
    }

    public function commit() {
        echo "commit \n";
        $this->_db->commit();
    }

    public function rollback() {
        echo "rollback \n";
        $this->_db->rollBack();
    }

    public function get_by_number($pattern) {
        $like = $pattern . '%';
        $db_name = $country . '_' . $area_code;

        if (!$limit && !$offset)
            $query = "SELECT * FROM `" . $db_name . "` WHERE `number` LIKE ?";

        $stmt = $this->_db->prepare($query);
        $stmt->execute(array($like));

        if ($stmt->rowCount())
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        else
            return false;
    }

    // Adding number in DB
    public function insert() {
        try {
            $stmt = $this->_db->prepare("INSERT INTO `" . $this->_db_name . "`(`number`, `provider`, `city`, `state`) VALUES(?, ?, ?, ?)");
            $stmt->execute(array($this->_number, $this->_provider, $this->_city, $this->_state));
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    public function delete_like_number($number) {
        $like = $number . '%';

        try {
            $stmt = $this->_db->prepare("DELETE FROM `" . $this->_db_name . "` WHERE `number` LIKE ?");
            $stmt->execute(array($like));
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }
}

 ?>